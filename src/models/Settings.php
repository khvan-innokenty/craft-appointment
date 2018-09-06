<?php
/**
 * Appointment plugin for Craft CMS 3.x
 *
 * Плагин для обработки формы с одним полем - телефон
 *
 * @link      medesse.com
 * @copyright Copyright (c) 2018 Medesse
 */

namespace medesse\appointment\models;

use medesse\appointment\Appointment;

use Craft;
use craft\base\Model;

/**
 * Appointment Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Medesse
 * @package   Appointment
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public $emailTo = '';
	public $roistatEnabled = false;
	public $roistatKey = '';
	public $cityFieldId = '';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['emailTo', 'string'],
            ['emailTo', 'default', 'value' => ''],
            ['roistatEnabled', 'boolean', 'trueValue' => true, 'falseValue' => false],
            ['roistatKey', 'string'],
            ['roistatKey', 'default', 'value' => ''],
            ['cityFieldId', 'string'],
            ['cityFieldId', 'default', 'value' => ''],
        ];
    }
}
