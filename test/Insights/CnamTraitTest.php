<?php

declare(strict_types=1);

namespace VonageTest\Insights;

use VonageTest\VonageTestCase;

class CnamTraitTest extends VonageTestCase
{
    /**
     *
     * @param $cnam
     * @param $inputData
     */
    public function testObjectAccess(): void
    {
        $inputData = [
            'first_name' => 'Tony',
            'last_name' => 'Tiger',
            'caller_name' => 'Tony Tiger Esq',
            'caller_type' => 'consumer'
        ];

        $cnam = new Cnam('14155550100');
        $cnam->fromArray($inputData);

        $this->assertEquals($inputData['first_name'], $cnam->getFirstName());
        $this->assertEquals($inputData['last_name'], $cnam->getLastName());
        $this->assertEquals($inputData['caller_name'], $cnam->getCallerName());
        $this->assertEquals($inputData['caller_type'], $cnam->getCallerType());
    }

    public function testCanHandleNullFields(): void
    {
        $inputData = [
            'first_name' => 'Tony',
            'last_name' => 'Tiger',
            'caller_name' => 'Tony Tiger Esq',
            'caller_type' => 'consumer'
        ];

        $inputFirstname = $inputData;
        unset($inputFirstname['first_name']);
        $firstName = new Cnam('14155550100');
        $firstName->fromArray($inputFirstname);
        $this->assertEquals(null, $firstName->getFirstName());

        $inputLastName = $inputData;
        unset($inputLastName['last_name']);
        $lastName = new Cnam('14155550100');
        $lastName->fromArray($inputLastName);
        $this->assertEquals(null, $lastName->getLastName());

        $inputCallerName = $inputData;
        unset($inputCallerName['caller_name']);
        $callerName = new Cnam('14155550100');
        $callerName->fromArray($inputCallerName);
        $this->assertEquals(null, $callerName->getCallerName());

        $inputCallerType = $inputData;
        unset($inputCallerType['caller_type']);
        $callerType = new Cnam('14155550100');
        $callerType->fromArray($inputCallerType);
        $this->assertEquals(null, $callerType->getCallerType());
    }
}
