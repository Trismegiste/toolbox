<?php

/*
 * Toolbox
 */

use MongoDB\BSON\ObjectId;
use Tests\Fixtures\Hadron;
use Tests\Fixtures\Lepton;
use Tests\Fixtures\Nucleus;
use Tests\Fixtures\Quark;
use Tests\Toolbox\MongoDb\MongoTestable;

class PersistableImplTest extends MongoTestable
{

    public function testDefaultWithoutRoot()
    {
        $obj = $this->resetWriteAndRead(new Lepton("muon"));
        $this->assertInstanceOf(ObjectId::class, $obj->_id);
        $this->assertEquals('muon', $obj->getName());
    }

    public function testAggregateWithoutRoot()
    {
        $doc = new Hadron("proton", [
            new Quark("up", 2 / 3),
            new Quark("up", 2 / 3),
            new Quark("down", -1 / 3)
        ]);

        $fromDb = $this->resetWriteAndRead($doc);
        $this->assertEquals("proton", $fromDb->getName());
        $this->assertEquals(1.0, $fromDb->getElectricCharge());
        $this->assertValidMongoId($fromDb->_id);
    }

    public function testComplexWithoutRoot()
    {
        $up = new Quark('up', 2 / 3);
        $down = new Quark('down', -1 / 3);
        $proton = new Hadron('proton', [$up, $up, $down]);
        $neutron = new Hadron('neutron', [$up, $down, $down]);
        $helion = new Nucleus([$proton, $proton, $neutron, $neutron]); // note : this is reference

        $fromDb = $this->resetWriteAndRead($helion);  // note : this is copy
        $this->assertEquals(2.0, $fromDb->getElectricCharge());
        $this->assertEquals(2, $fromDb->getAtomicNumber());
        $this->assertValidMongoId($fromDb->_id);
    }

    public function testMongoType()
    {
        $obj = new \Tests\Fixtures\Internal();
        $obj->_id = new ObjectId();
        $obj->dob = new DateTime("1993-07-07");
        $obj->arr = ['data' => 42];
        $fromDb = $this->resetWriteAndRead($obj);
        $this->assertEquals($obj, $fromDb);
    }

}
