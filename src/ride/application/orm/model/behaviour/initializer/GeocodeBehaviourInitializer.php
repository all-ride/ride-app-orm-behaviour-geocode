<?php

namespace ride\application\orm\model\behaviour\initializer;

use ride\application\orm\model\behaviour\GeocodeBehaviour;

use ride\library\generator\CodeClass;
use ride\library\generator\CodeGenerator;
use ride\library\orm\definition\field\PropertyField;
use ride\library\orm\definition\ModelTable;
use ride\library\orm\model\behaviour\initializer\BehaviourInitializer;
use ride\library\reflection\Boolean;

use \InvalidArgumentException;

/**
 * Setup the geocode behaviour based on the model options
 */
class GeocodeBehaviourInitializer implements BehaviourInitializer {
    protected $service;

    /**
     * Constructs a new instance
     * @param string $service Name of the service inside
     * @return null
     */
    public function __construct($service = 'address') {
        $this->service = $service;
    }

    /**
     * Gets the behaviours for the model of the provided model table
     * @param \ride\library\orm\definition\ModelTable $modelTable
     * @return array An array with instances of Behaviour
     * @see \ride\library\orm\model\behaviour\Behaviour
     */
    public function getBehavioursForModel(ModelTable $modelTable) {
        if (!$modelTable->getOption('behaviour.geo')) {
            return array();
        }

        if (!$modelTable->hasField('latitude')) {
            $latitudeField = new PropertyField('latitude', 'float');
            $latitudeField->setOptions(array(
                'label' => 'label.latitude',
                'scaffold.form.omit' => 'true',
            ));

            $modelTable->addField($latitudeField);
        }

        if (!$modelTable->hasField('longitude')) {
            $longitudeField = new PropertyField('longitude', 'float');
            $longitudeField->setOptions(array(
                'label' => 'label.longitude',
                'scaffold.form.omit' => 'true',
            ));

            $modelTable->addField($longitudeField);
        }

        return array(new GeocodeBehaviour($this->service));
    }

    /**
     * Generates the needed code for the entry class of the provided model table
     * @param \ride\library\orm\definition\ModelTable $table
     * @param \ride\library\generator\CodeGenerator $generator
     * @param \ride\library\generator\CodeClass $class
     * @return null
     */
    public function generateEntryClass(ModelTable $modelTable, CodeGenerator $generator, CodeClass $class) {
        $geoValue = $modelTable->getOption('behaviour.geo');
        if (!$geoValue) {
            return;
        }

        $class->addImplements('ride\\application\\orm\\entry\\GeocodedEntry');

        try {
            Boolean::getBoolean($geoValue);
        } catch (InvalidArgumentException $exception) {
            $fields = explode(',', $geoValue);

            if (count($fields) == 1) {
                $field = array_pop($fields);

                $addressCode = 'return $this->get' . ucfirst($field) . '();';
            } else {
                $addressCode = "\$address = '';\n";
                foreach ($fields as $field) {
                    $addressCode .= '$address .= \' \' . $this->get' . ucfirst(trim($field)) . "();\n";
                }
                $addressCode .= "\nreturn trim(\$address);";
            }

            $addressMethod = $generator->createMethod('getGeocodeAddress', array(), $addressCode);
            $addressMethod->setDescription('Gets the address to lookup the coordinates for');
            $addressMethod->setReturnValue($generator->createVariable('result', 'string'));

            $class->addMethod($addressMethod);
        }
    }

}
