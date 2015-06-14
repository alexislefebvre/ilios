<?php

namespace Ilios\CoreBundle\Controller;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Ilios\CoreBundle\Exception\InvalidFormException;
use Ilios\CoreBundle\Handler\SessionLearningMaterialHandler;
use Ilios\CoreBundle\Entity\SessionLearningMaterialInterface;

/**
 * Class SessionLearningMaterialController
 * @package Ilios\CoreBundle\Controller
 * @RouteResource("SessionLearningMaterials")
 */
class SessionLearningMaterialController extends FOSRestController
{
    /**
     * Get a SessionLearningMaterial
     *
     * @ApiDoc(
     *   section = "SessionLearningMaterial",
     *   description = "Get a SessionLearningMaterial.",
     *   resource = true,
     *   requirements={
     *     {
     *        "name"="id",
     *        "dataType"="integer",
     *        "requirement"="\d+",
     *        "description"="SessionLearningMaterial identifier."
     *     }
     *   },
     *   output="Ilios\CoreBundle\Entity\SessionLearningMaterial",
     *   statusCodes={
     *     200 = "SessionLearningMaterial.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return Response
     */
    public function getAction($id)
    {
        $answer['sessionLearningMaterials'][] = $this->getOr404($id);

        return $answer;
    }

    /**
     * Get all SessionLearningMaterial.
     *
     * @ApiDoc(
     *   section = "SessionLearningMaterial",
     *   description = "Get all SessionLearningMaterial.",
     *   resource = true,
     *   output="Ilios\CoreBundle\Entity\SessionLearningMaterial",
     *   statusCodes = {
     *     200 = "List of all SessionLearningMaterial",
     *     204 = "No content. Nothing to list."
     *   }
     * )
     *
     * @QueryParam(
     *   name="offset",
     *   requirements="\d+",
     *   nullable=true,
     *   description="Offset from which to start listing notes."
     * )
     * @QueryParam(
     *   name="limit",
     *   requirements="\d+",
     *   default="20",
     *   description="How many notes to return."
     * )
     * @QueryParam(
     *   name="order_by",
     *   nullable=true,
     *   array=true,
     *   description="Order by fields. Must be an array ie. &order_by[name]=ASC&order_by[description]=DESC"
     * )
     * @QueryParam(
     *   name="filters",
     *   nullable=true,
     *   array=true,
     *   description="Filter by fields. Must be an array ie. &filters[id]=3"
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');
        $orderBy = $paramFetcher->get('order_by');
        $criteria = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : [];
        $criteria = array_map(function ($item) {
            $item = $item == 'null' ? null : $item;
            $item = $item == 'false' ? false : $item;
            $item = $item == 'true' ? true : $item;

            return $item;
        }, $criteria);

        $result = $this->getSessionLearningMaterialHandler()
            ->findSessionLearningMaterialsBy(
                $criteria,
                $orderBy,
                $limit,
                $offset
            );

        //If there are no matches return an empty array
        $answer['sessionLearningMaterials'] =
            $result ? $result : new ArrayCollection([]);

        return $answer;
    }

    /**
     * Create a SessionLearningMaterial.
     *
     * @ApiDoc(
     *   section = "SessionLearningMaterial",
     *   description = "Create a SessionLearningMaterial.",
     *   resource = true,
     *   input="Ilios\CoreBundle\Form\Type\SessionLearningMaterialType",
     *   output="Ilios\CoreBundle\Entity\SessionLearningMaterial",
     *   statusCodes={
     *     201 = "Created SessionLearningMaterial.",
     *     400 = "Bad Request.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @Rest\View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        try {
            $new  =  $this->getSessionLearningMaterialHandler()
                ->post($this->getPostData($request));
            $answer['sessionLearningMaterials'] = [$new];

            return $answer;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update a SessionLearningMaterial.
     *
     * @ApiDoc(
     *   section = "SessionLearningMaterial",
     *   description = "Update a SessionLearningMaterial entity.",
     *   resource = true,
     *   input="Ilios\CoreBundle\Form\Type\SessionLearningMaterialType",
     *   output="Ilios\CoreBundle\Entity\SessionLearningMaterial",
     *   statusCodes={
     *     200 = "Updated SessionLearningMaterial.",
     *     201 = "Created SessionLearningMaterial.",
     *     400 = "Bad Request.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function putAction(Request $request, $id)
    {
        try {
            $sessionLearningMaterial = $this->getSessionLearningMaterialHandler()
                ->findSessionLearningMaterialBy(['id'=> $id]);
            if ($sessionLearningMaterial) {
                $code = Codes::HTTP_OK;
            } else {
                $sessionLearningMaterial = $this->getSessionLearningMaterialHandler()
                    ->createSessionLearningMaterial();
                $code = Codes::HTTP_CREATED;
            }

            $answer['sessionLearningMaterial'] =
                $this->getSessionLearningMaterialHandler()->put(
                    $sessionLearningMaterial,
                    $this->getPostData($request)
                );
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

        $view = $this->view($answer, $code);

        return $this->handleView($view);
    }

    /**
     * Partial Update to a SessionLearningMaterial.
     *
     * @ApiDoc(
     *   section = "SessionLearningMaterial",
     *   description = "Partial Update to a SessionLearningMaterial.",
     *   resource = true,
     *   input="Ilios\CoreBundle\Form\Type\SessionLearningMaterialType",
     *   output="Ilios\CoreBundle\Entity\SessionLearningMaterial",
     *   requirements={
     *     {
     *         "name"="id",
     *         "dataType"="integer",
     *         "requirement"="\d+",
     *         "description"="SessionLearningMaterial identifier."
     *     }
     *   },
     *   statusCodes={
     *     200 = "Updated SessionLearningMaterial.",
     *     400 = "Bad Request.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function patchAction(Request $request, $id)
    {
        $answer['sessionLearningMaterial'] =
            $this->getSessionLearningMaterialHandler()->patch(
                $this->getOr404($id),
                $this->getPostData($request)
            );

        return $answer;
    }

    /**
     * Delete a SessionLearningMaterial.
     *
     * @ApiDoc(
     *   section = "SessionLearningMaterial",
     *   description = "Delete a SessionLearningMaterial entity.",
     *   resource = true,
     *   requirements={
     *     {
     *         "name" = "id",
     *         "dataType" = "integer",
     *         "requirement" = "\d+",
     *         "description" = "SessionLearningMaterial identifier"
     *     }
     *   },
     *   statusCodes={
     *     204 = "No content. Successfully deleted SessionLearningMaterial.",
     *     400 = "Bad Request.",
     *     404 = "Not found."
     *   }
     * )
     *
     * @Rest\View(statusCode=204)
     *
     * @param $id
     * @internal SessionLearningMaterialInterface $sessionLearningMaterial
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $sessionLearningMaterial = $this->getOr404($id);

        try {
            $this->getSessionLearningMaterialHandler()
                ->deleteSessionLearningMaterial($sessionLearningMaterial);

            return new Response('', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $exception) {
            throw new \RuntimeException("Deletion not allowed");
        }
    }

    /**
     * Get a entity or throw a exception
     *
     * @param $id
     * @return SessionLearningMaterialInterface $sessionLearningMaterial
     */
    protected function getOr404($id)
    {
        $sessionLearningMaterial = $this->getSessionLearningMaterialHandler()
            ->findSessionLearningMaterialBy(['id' => $id]);
        if (!$sessionLearningMaterial) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        return $sessionLearningMaterial;
    }

    /**
     * Parse the request for the form data
     *
     * @param Request $request
     * @return array
     */
    protected function getPostData(Request $request)
    {
        $data = $request->request->get('sessionLearningMaterial');

        if (empty($data)) {
            $data = $request->request->all();
        }

        return $data;
    }

    /**
     * @return SessionLearningMaterialHandler
     */
    protected function getSessionLearningMaterialHandler()
    {
        return $this->container->get('ilioscore.sessionlearningmaterial.handler');
    }
}
