<?php
/**
 * Created by Gorlum 10.08.2016 14:25
 */

namespace V2Unit;
use Common\GlobalContainer;

/**
 * Class V2UnitContainer
 *
 * @method V2UnitModel getModel()
 *
 * @property int       $playerOwnerId
 * @property int       $locationType
 * @property int       $locationId
 * @property int       $type
 * @property int       $snId
 * @property int       $level - level of unit for DB: $count for stackable units, $level - fon unstackable units
 * property int $count // TODO
 * @property \DateTime $timeStart
 * @property \DateTime $timeFinish
 * @property bool      $isStackable
 * @property string    $locationDefaultType
 * @property int       $bonusType // TODO - Optional?
 * @property array     $unitInfo - full info about unit
 *
 * @package V2Unit
 */
class V2UnitContainer extends \EntityContainer {
  /**
   * @var V2UnitModel $model
   */
  protected $model;

  protected static $exceptionClass = 'EntityException';
  protected static $modelClass = 'V2Unit\V2UnitModel';

  /**
   * Name of table for this entity
   *
   * @var string $tableName
   */
  protected $tableName = 'unit';
  /**
   * Name of key field field in this table
   *
   * @var string $idField
   */
  protected $idField = 'unit_id';
  /**
   * Property list
   *
   * @var array $properties
   */
  protected $properties = array(
    'dbId'                => array(
      P_DB_FIELD => 'unit_id',
    ),
    'playerOwnerId'       => array(
      P_DB_FIELD => 'unit_player_id',
    ),
    'locationType'        => array(
      P_DB_FIELD => 'unit_location_type',
    ),
    'locationId'          => array(
      P_DB_FIELD => 'unit_location_id',
    ),
    'type'                => array(
      P_DB_FIELD => 'unit_type',
    ),
    'snId'                => array(
      P_DB_FIELD => 'unit_snid',
    ),
    // Order is important!
    // TODO - split dbLevel to level and count
    'level'               => array(
      P_DB_FIELD => 'unit_level',
    ),
    'count'               => array(),
    // TODO - move to child class
    'timeStart'           => array(
      P_DB_FIELD => 'unit_time_start',
    ),
    'timeFinish'          => array(
      P_DB_FIELD => 'unit_time_finish',
    ),
    // Do we need it? Or internal no info/getters/setters should be ignored?
    'unitInfo'            => array(),
    'isStackable'         => array(),
    'locationDefaultType' => array(),
    'bonusType'           => array(),
  );

  /**
   * BuddyContainer constructor.
   *
   * @param GlobalContainer $gc
   */
  public function __construct($gc) {
    parent::__construct($gc);

    $that = $this;

    $this->assignAccessor('type', P_CONTAINER_SET,
      function ($that, $value) {
        $that->type = $value;
        $array = get_unit_param($value);
        $that->unitInfo = $array;
        // Mandatory
        $that->isStackable = empty($array[P_STACKABLE]) ? false : true;
        $that->locationDefaultType = empty($array[P_LOCATION_DEFAULT]) ? LOC_NONE : $array[P_LOCATION_DEFAULT];
        // Optional
        $that->bonusType = empty($array[P_BONUS_TYPE]) ? BONUS_NONE : $array[P_BONUS_TYPE];
      }
    );

    // This crap code is until php 5.4+. There we can use $this binding for lambdas
    $fieldName = $this->properties[$propertyName = 'timeStart'][P_DB_FIELD];
    $this->assignAccessor($propertyName, P_CONTAINER_IMPORT,
      function (V2UnitContainer $that, &$row, $propertyName, $fieldName) use ($fieldName) {
        if (isset($row[$fieldName])) {
          $dateTime = new \DateTime($row[$fieldName]);
        } else {
          $dateTime = null;
        }
        $that->$propertyName = $dateTime;
      }
    );
    $this->assignAccessor($propertyName, P_CONTAINER_EXPORT,
      function (V2UnitContainer $that, &$row, $propertyName, $fieldName) use ($fieldName) {
        $dateTime = $that->$propertyName;
        if ($dateTime instanceof \DateTime) {
          $row[$fieldName] = $dateTime->format(FMT_DATE_TIME_SQL);
        } else {
          $row[$fieldName] = null;
        }
      }
    );

    $fieldName = $this->properties[$propertyName = 'timeFinish'][P_DB_FIELD];
    $this->assignAccessor($propertyName, P_CONTAINER_IMPORT,
      function (V2UnitContainer $that, &$row, $propertyName, $fieldName) {
        if (isset($row[$fieldName])) {
          $dateTime = new \DateTime($row[$fieldName]);
        } else {
          $dateTime = null;
        }
        $that->$propertyName = $dateTime;
      }
    );
    $this->assignAccessor($propertyName, P_CONTAINER_EXPORT,
      function (V2UnitContainer $that, &$row, $propertyName, $fieldName) {
        $dateTime = $that->$propertyName;
        if ($dateTime instanceof \DateTime) {
          $row[$fieldName] = $dateTime->format(FMT_DATE_TIME_SQL);
        } else {
          $row[$fieldName] = null;
        }
      }
    );

  }

  public function isEmpty() {
    return
      empty($this->playerOwnerId)
      ||
      is_null($this->locationType)
      ||
      $this->locationType === LOC_NONE
      ||
      empty($this->locationId)
      ||
      empty($this->type)
      ||
      empty($this->snId)
      ||
      empty($this->level);
  }

}