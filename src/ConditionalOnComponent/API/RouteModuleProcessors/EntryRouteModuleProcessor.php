<?php

declare(strict_types=1);

namespace PoPSchema\PostCategories\ConditionalOnComponent\API\RouteModuleProcessors;

use PoP\Root\App;
use PoP\API\Response\Schemes as APISchemes;
use PoP\ModuleRouting\AbstractEntryRouteModuleProcessor;
use PoP\Root\Routing\RouteNatures;
use PoPSchema\Categories\Routing\RouteNatures as CategoryRouteNatures;
use PoPSchema\PostCategories\Component;
use PoPSchema\PostCategories\ComponentConfiguration;
use PoPSchema\PostCategories\ConditionalOnComponent\API\ModuleProcessors\CategoryPostFieldDataloadModuleProcessor;
use PoPSchema\PostCategories\ConditionalOnComponent\API\ModuleProcessors\PostCategoryFieldDataloadModuleProcessor;
use PoPSchema\PostCategories\TypeAPIs\PostCategoryTypeAPIInterface;
use PoPSchema\Posts\Component as PostsComponent;
use PoPSchema\Posts\ComponentConfiguration as PostsComponentConfiguration;

class EntryRouteModuleProcessor extends AbstractEntryRouteModuleProcessor
{
    private ?PostCategoryTypeAPIInterface $postCategoryTypeAPI = null;

    final public function setPostCategoryTypeAPI(PostCategoryTypeAPIInterface $postCategoryTypeAPI): void
    {
        $this->postCategoryTypeAPI = $postCategoryTypeAPI;
    }
    final protected function getPostCategoryTypeAPI(): PostCategoryTypeAPIInterface
    {
        return $this->postCategoryTypeAPI ??= $this->instanceManager->getInstance(PostCategoryTypeAPIInterface::class);
    }

    /**
     * @return array<string, array<array>>
     */
    public function getModulesVarsPropertiesByNature(): array
    {
        $ret = array();
        $ret[CategoryRouteNatures::CATEGORY][] = [
            'module' => [PostCategoryFieldDataloadModuleProcessor::class, PostCategoryFieldDataloadModuleProcessor::MODULE_DATALOAD_RELATIONALFIELDS_CATEGORY],
            'conditions' => [
                'scheme' => APISchemes::API,
                'routing' => [
                    'taxonomy-name' => $this->getPostCategoryTypeAPI()->getPostCategoryTaxonomyName(),
                ],
            ],
        ];
        return $ret;
    }

    /**
     * @return array<string, array<string, array<array>>>
     */
    public function getModulesVarsPropertiesByNatureAndRoute(): array
    {
        $ret = array();
        /** @var ComponentConfiguration */
        $componentConfiguration = App::getComponent(Component::class)->getConfiguration();
        $routemodules = array(
            $componentConfiguration->getPostCategoriesRoute() => [PostCategoryFieldDataloadModuleProcessor::class, PostCategoryFieldDataloadModuleProcessor::MODULE_DATALOAD_RELATIONALFIELDS_CATEGORYLIST],
        );
        foreach ($routemodules as $route => $module) {
            $ret[RouteNatures::GENERIC][$route][] = [
                'module' => $module,
                'conditions' => [
                    'scheme' => APISchemes::API,
                ],
            ];
        }
        /** @var PostsComponentConfiguration */
        $componentConfiguration = App::getComponent(PostsComponent::class)->getConfiguration();
        $routemodules = array(
            $componentConfiguration->getPostsRoute() => [CategoryPostFieldDataloadModuleProcessor::class, CategoryPostFieldDataloadModuleProcessor::MODULE_DATALOAD_RELATIONALFIELDS_CATEGORYPOSTLIST],
        );
        foreach ($routemodules as $route => $module) {
            $ret[CategoryRouteNatures::CATEGORY][$route][] = [
                'module' => $module,
                'conditions' => [
                    'scheme' => APISchemes::API,
                    'routing' => [
                        'taxonomy-name' => $this->getPostCategoryTypeAPI()->getPostCategoryTaxonomyName(),
                    ],
                ],
            ];
        }
        return $ret;
    }
}
