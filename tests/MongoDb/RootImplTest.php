<?php

/*
 * Toolbox
 */

namespace Tests\Toolbox\MongoDb;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;
use Tests\Fixtures\Atom;
use Tests\Fixtures\Hadron;
use Tests\Fixtures\Lepton;
use Tests\Fixtures\Nucleus;
use Tests\Fixtures\Quark;

/**
 * Description of RootImplTest
 */
class RootImplTest extends MongoTestable {

    public function testComplexWithRoot() {
        $up = new Quark('up', 2 / 3);
        $down = new Quark('down', -1 / 3);
        $proton = new Hadron('proton', [$up, $up, $down]);
        $neutron = new Hadron('neutron', [$up, $down, $down]);
        $helion = new Nucleus([$proton, $proton, $neutron, $neutron]);
        $electron = new Lepton('electron');
        $atom = new Atom($helion, [$electron, $electron]);

        $fromDb = $this->resetWriteAndRead($atom);
        $this->assertFalse($fromDb->isIonized());
        $this->assertValidMongoId($fromDb->getPk());

        $fromDb->looseElectron();
        $bulk = new BulkWrite();
        $bulk->update(['_id' => $fromDb->getPk()], $fromDb);
        $this->mongo->executeBulkWrite('testdb.collection', $bulk);

        $cursor = $this->mongo->executeQuery('testdb.collection', new Query([]));
        $fromDb = iterator_to_array($cursor);
        $this->assertCount(1, $fromDb);

        $ion = $fromDb[0];
        $this->assertTrue($ion->isIonized());
    }

}