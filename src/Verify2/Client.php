<?php

namespace Vonage\Verify2;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception;
use Vonage\Client\Exception\Request;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Entity\IterableAPICollection;
use Vonage\Verify2\Filters\TemplateFilter;
use Vonage\Verify2\Request\BaseVerifyRequest;
use Vonage\Verify2\Request\CreateCustomTemplateFragmentRequest;
use Vonage\Verify2\Request\SilentAuthRequest;
use Vonage\Verify2\Request\UpdateCustomTemplateRequest;
use Vonage\Verify2\VerifyObjects\Template;
use Vonage\Verify2\VerifyObjects\TemplateFragment;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function startVerification(BaseVerifyRequest $request): ?array
    {
        if (self::isSilentAuthRequest($request)) {
            if (SilentAuthRequest::isValidWorkflow($request->getWorkflows())) {
                return $this->getAPIResource()->create($request->toArray());
            }

            throw new \InvalidArgumentException('Silent Auth must be the first workflow if used');
        }

        return $this->getAPIResource()->create($request->toArray());
    }

    public function check(string $requestId, $code): bool
    {
        try {
            $response = $this->getAPIResource()->create(['code' => $code], '/' . $requestId);
        } catch (Exception $e) {
            // For horrible reasons in the API Error Handler, throw the error unless it's a 409.
            if ($e->getCode() === 409) {
                throw new Request('Conflict: The current Verify workflow step does not support a code.');
            }

            throw $e;
        }

        return true;
    }

    public function cancelRequest(string $requestId): bool
    {
        $this->api->delete($requestId);

        return true;
    }

    public function nextWorkflow(string $requestId): bool
    {
        $this->api->create([], '/' . $requestId . '/next_workflow');

        return true;
    }

    public static function isSilentAuthRequest(BaseVerifyRequest $request): bool
    {
        foreach ($request->getWorkflows() as $workflow) {
            if ($workflow['channel'] == VerificationWorkflow::WORKFLOW_SILENT_AUTH) {
                return true;
            }
        }

        return false;
    }

    public function listCustomTemplates(?TemplateFilter $filter = null): IterableAPICollection
    {
        $collection = $this->api->search($filter, '/templates');
        $collection->setNaiveCount(true);
        $collection->setPageIndexKey('page');

        if (is_null($filter)) {
            $collection->setNoQueryParameters(true);
        }

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Template());
        $collection->setHydrator($hydrator);

        return $collection;
    }

    public function createCustomTemplate(string $name): Template
    {
        $response = $this->api->create([
            'name' => $name,
        ], '/templates');

        $template = new Template();
        return $template->fromArray($response);
    }

    public function getCustomTemplate(string $templateId): Template
    {
        $response = $this->api->get('templates/' . $templateId);
        $template = new Template();
        return $template->fromArray($response);
    }

    public function deleteCustomTemplate(string $templateId): bool
    {
        $this->api->delete('templates/' . $templateId);
        return true;
    }

    public function updateCustomTemplate($templateId, UpdateCustomTemplateRequest $request): Template
    {
        $response = $this->api->partiallyUpdate('templates/' . $templateId, $request->toArray());
        $template = new Template();
        return $template->fromArray($response);
    }

    public function createCustomTemplateFragment(string $templateId, CreateCustomTemplateFragmentRequest $createTemplateFragmentRequest): TemplateFragment
    {
        $response = $this->api->create($createTemplateFragmentRequest->toArray(), '/templates/' . $templateId . '/template_fragments');
        $templateFragment = new TemplateFragment();
        $templateFragment->fromArray($response);

        return $templateFragment;
    }

    public function getCustomTemplateFragment(string $templateId, string $fragmentId): TemplateFragment
    {
        $response = $this->api->get('templates/' . $templateId . '/template_fragments/' . $fragmentId);
        $templateFragment = new TemplateFragment();
        return $templateFragment->fromArray($response);
    }

    public function updateCustomTemplateFragment(string $templateId, string $fragmentId, string $text): TemplateFragment
    {
        $response = $this->api->partiallyUpdate('templates/' . $templateId . '/template_fragments/' . $fragmentId, ['text' => $text]);
        $templateFragment = new TemplateFragment();
        return $templateFragment->fromArray($response);
    }

    public function deleteCustomTemplateFragment(string $templateId, string $fragmentId): bool
    {
        $this->api->delete('templates/' . $templateId . '/template_fragments/' . $fragmentId);
        return true;
    }

    public function listTemplateFragments(string $templateId, ?TemplateFilter $filter = null): IterableAPICollection
    {
        $api = clone $this->getAPIResource();
        $api->setCollectionName('template_fragments');

        $collection = $api->search($filter, '/templates/' . $templateId . '/template_fragments');
        $collection->setNaiveCount(true);
        $collection->setPageIndexKey('page');

        if (is_null($filter)) {
            $collection->setNoQueryParameters(true);
        }

        if (!is_null($filter)) {
            if ($filter->getQuery()['page']) {
                $collection->setAutoAdvance(false);
                $collection->setIndex($filter->getQuery()['page']);
            }
        }

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new TemplateFragment());
        $collection->setHydrator($hydrator);

        return $collection;
    }
}
