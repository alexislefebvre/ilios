<?php

declare(strict_types=1);

namespace App\Tests\Endpoints;

use App\Tests\Fixture\LoadAamcResourceTypeData;
use App\Tests\Fixture\LoadTermData;
use Exception;

/**
 * AamcResourceType API endpoint Test.
 * @group api_3
 */
class AamcResourceTypeTest extends AbstractReadWriteEndpoint
{
    protected string $testName =  'aamcResourceTypes';
    protected bool $enableDeleteTestsWithServiceToken = false;
    protected bool $enablePatchTestsWithServiceToken = false;
    protected bool $enablePostTestsWithServiceToken = false;
    protected bool $enablePutTestsWithServiceToken = false;

    protected function getFixtures(): array
    {
        return [
            LoadAamcResourceTypeData::class,
            LoadTermData::class
        ];
    }

    /**
     * @inheritDoc
     */
    public function putsToTest(): array
    {
        return [
            'title' => ['title', 'sure thing'],
            'description' => ['description', 'lorem ipsum'],
            'terms' => ['terms', [3]],
            'id' => ['id', 'FK1', true],
        ];
    }

    /**
     * @inheritDoc
     */
    public function readOnlyPropertiesToTest(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function filtersToTest(): array
    {
        return [
            'id' => [[2], ['id' => 'RE003']],
            'title' => [[0], ['title' => 'first title']],
            'description' => [[1], ['description' => 'second description']],
            'terms' => [[0], ['terms' => [1]]],
        ];
    }

    /**
     * @throws Exception
     */
    public function testPostTermAamcResourceType(): void
    {
        $jwt = $this->createJwtForRootUser($this->kernelBrowser);
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->create();
        $postData = $data;
        $this->relatedPostDataTest($data, $postData, $jwt, 'aamcResourceType', 'terms');
    }

    /**
     * @throws Exception
     */
    public function testPutResourceTypeWithExtraData(): void
    {
        $jwt = $this->createJwtForRootUser($this->kernelBrowser);
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->create();
        $data['garbage'] = 'LA Dodgers';

        $this->badPostTest($data, $jwt);
    }

    /**
     * @throws Exception
     */
    public function testAccessDeniedWithServiceToken(): void
    {
        $jwt = $this->createJwtFromServiceTokenWithWriteAccessInAllSchools(
            $this->kernelBrowser,
            $this->fixtures
        );
        $data = $this->getDataLoader()->getOne();
        $this->canNot(
            $this->kernelBrowser,
            $jwt,
            'DELETE',
            $this->getUrl(
                $this->kernelBrowser,
                'app_api_aamcresourcetypes_delete',
                ['version' => $this->apiVersion, 'id' => $data['id']],
            ),
        );
        $this->canNot(
            $this->kernelBrowser,
            $jwt,
            'POST',
            $this->getUrl(
                $this->kernelBrowser,
                'app_api_aamcresourcetypes_post',
                ['version' => $this->apiVersion],
            ),
            json_encode([])
        );
        $this->canNotJsonApi(
            $this->kernelBrowser,
            $jwt,
            'POST',
            $this->getUrl(
                $this->kernelBrowser,
                'app_api_aamcresourcetypes_post',
                ['version' => $this->apiVersion],
            ),
            json_encode([])
        );
        $this->canNot(
            $this->kernelBrowser,
            $jwt,
            'PUT',
            $this->getUrl(
                $this->kernelBrowser,
                'app_api_aamcresourcetypes_put',
                ['version' => $this->apiVersion, 'id' => $data['id']],
            ),
            json_encode([])
        );
        $this->canNotJsonApi(
            $this->kernelBrowser,
            $jwt,
            'PATCH',
            $this->getUrl(
                $this->kernelBrowser,
                'app_api_aamcresourcetypes_patch',
                ['version' => $this->apiVersion, 'id' => $data['id']],
            ),
            json_encode([])
        );
    }
}
