<?php
/**
 * Appointment plugin for Craft CMS 3.x
 *
 * Плагин для обработки формы с одним полем - телефон
 *
 * @link      medesse.com
 * @copyright Copyright (c) 2018 Medesse
 */

namespace medesse\appointment\controllers;

use medesse\appointment\Appointment;

use Craft;
use craft\web\Controller;
use craft\mail\Message;
use craft\elements\User;
use yii\base\InvalidConfigException;



/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Medesse
 * @package   Appointment
 * @since     1.0.0
 */
class SendController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/appointment/send
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $mailer = Craft::$app->getMailer();

        $phone = isset($_REQUEST['phone']) ? preg_replace("/[^\d\s\+\-]/ui", "", $_REQUEST['phone']) : "";
        $source_url = isset($_REQUEST['url']) ? filter_var($_REQUEST['url'], FILTER_SANITIZE_URL) : "";

        $textBody = "Телефон: $phone\r\nИсточник: $source_url";

        $message = (new Message())
            ->setFrom($mailer->from)
            ->setSubject("Заявка с сайта")
            ->setTextBody($textBody);

        $emails = explode(',', Appointment::getInstance()->getSettings()->emailTo);
        $emails = array_map('trim', $emails);

        $roistatEnabled = Appointment::getInstance()->getSettings()->roistatEnabled;
        $roistatKey = trim(Appointment::getInstance()->getSettings()->roistatKey);
        $cityFildId = trim(Appointment::getInstance()->getSettings()->cityFieldId);

        if ($roistatEnabled && $roistatKey) {
            $fields = $cityFildId ?
                [
                    $cityFildId => '{city}'
                ] :
                [];

            $roistatData = [
                'roistat' => isset($_COOKIE['roistat_visit']) ? $_COOKIE['roistat_visit'] : null,
                'key'     => $roistatKey,
                'title'   => $phone,
                'comment' => 'Запрос с сайта',
                'phone'   => $phone,
                'is_need_callback' => '0',
                'sync'    => '0',
                'is_need_check_order_in_processing' => '1', // Включение проверки заявок на дубли
                'is_need_check_order_in_processing_append' => '1', // Если создана дублирующая заявка, в нее будет добавлен комментарий об этом
                'fields'  => $fields
            ];

            file_get_contents("https://cloud.roistat.com/api/proxy/1.0/leads/add?" . http_build_query($roistatData));
        }

        $message->setTo( $emails );
        $result = $mailer->send($message);

        return json_encode([
            'success' => $result
        ]);
    }

    /**
     * Returns the "From" email value on the given mailer $from property object.
     *
     * @param string|array|User|User[]|null $from
     *
     * @return string
     * @throws InvalidConfigException if it can’t be determined
     */
    public function getFromEmail($from): string
    {
        if (is_string($from)) {
            return $from;
        }
        if ($from instanceof User) {
            return $from->email;
        }
        if (is_array($from)) {
            $first = reset($from);
            $key = key($from);
            if (is_numeric($key)) {
                return $this->getFromEmail($first);
            }
            return $key;
        }
        throw new InvalidConfigException('Can\'t determine "From" email from email config settings.');
    }
}
