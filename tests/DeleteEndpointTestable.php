<?php

namespace Tests\App;

/**
 * Trait DeleteEndpointTestable
 * @package Tests\App
 */
trait DeleteEndpointTestable
{
    /**
     * @see DeleteEndpointTestInterface::testDelete
     */
    public function testDelete()
    {
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->getOne();
        $this->deleteTest($data['id']);
    }
}
