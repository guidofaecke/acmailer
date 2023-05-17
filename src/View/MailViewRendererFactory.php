<?php

declare(strict_types=1);

namespace AcMailer\View;

use interop\container\containerinterface;
use interop\container\exception\containerexception;
use interop\container\exception\notfoundexception;
use Laminas\Mvc\Service\ViewHelperManagerFactory;
use Laminas\ServiceManager\Config;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerExceptionInterface;

use function array_shift;
use function assert;
use function count;

class MailViewRendererFactory
{
    /** @deprecated Use the MailViewRendererInterface FQCN instead */
    public const SERVICE_NAME = MailViewRendererInterface::class;

    /**
     * @throws ContainerExceptionInterface
     * @throws containerexception
     * @throws notfoundexception
     */
    public function __invoke(containerinterface $container): MailViewRendererInterface
    {
        // First, if the TemplateRendererInterface is registered as a service, use that service.
        // This should be true in expressive applications
        if ($container->has(TemplateRendererInterface::class)) {
            $templateRendererInterface = $container->get(TemplateRendererInterface::class);
            assert($templateRendererInterface instanceof TemplateRendererInterface);

            return new MezzioMailViewRenderer($templateRendererInterface);
        }

        // If the mailviewrenderer is registered, wrap it into a LaminasViewRenderer
        // This should be true in Laminas/MVC apps, run in a HTTP context
        if ($container->has('mailviewrenderer')) {
            $mailViewRenderer = $container->get('mailviewrenderer');
            assert($mailViewRenderer instanceof RendererInterface);

            return $this->wrapLaminasView($mailViewRenderer);
        }

        // Finally, create a laminas/view PhpRenderer and wrap it into a LaminasViewRenderer
        // This should be reached only in Laminas/MVC apps run in a CLI context
        $vmConfig = $this->getSpecificConfig($container, 'view_manager');
        $renderer = new PhpRenderer();

        // Check what kind of view_manager configuration has been defined
        $resolversStack = [];
        if (isset($vmConfig['template_map'])) {
            // Create a TemplateMapResolver in case only the template_map has been defined
            $resolversStack[] = new TemplateMapResolver($vmConfig['template_map']);
        }
        if (isset($vmConfig['template_path_stack'])) {
            // Create a TemplatePathStack resolver in case only the template_path_stack has been defined
            $pathStackResolver = new TemplatePathStack();
            $pathStackResolver->setPaths($vmConfig['template_path_stack']);
            $resolversStack[] = $pathStackResolver;
        }

        // Create the template resolver for the PhpRenderer
        $resolver = $this->buildTemplateResolverFromStack($resolversStack);
        if ($resolver !== null) {
            $renderer->setResolver($resolver);
        }

        // Create a HelperPluginManager with default view helpers and user defined view helpers
        $renderer->setHelperPluginManager($this->createHelperPluginManager($container));
        return $this->wrapLaminasView($renderer);
    }

    private function wrapLaminasView(RendererInterface $renderer): MailViewRendererInterface
    {
        return new MvcMailViewRenderer($renderer);
    }

    /**
     * Creates a view helper manager
     *
     * @throws containerexception
     * @throws notfoundexception
     */
    private function createHelperPluginManager(containerinterface $container): HelperPluginManager
    {
        $factory = new ViewHelperManagerFactory();
        /** @var HelperPluginManager $helperManager */
        $helperManager = $factory($container, ViewHelperManagerFactory::PLUGIN_MANAGER_CLASS);
        $viewHelpers   = $this->getSpecificConfig($container, 'view_helpers');
        $config        = new Config($viewHelpers);
        $config->configureServiceManager($helperManager);
        return $helperManager;
    }

    /**
     * Returns a specific configuration defined by provided key
     *
     * @return array
     * @throws containerexception
     * @throws notfoundexception
     */
    private function getSpecificConfig(containerinterface $container, string $configKey): array
    {
        /** @var array $containerConfig */
        $containerConfig = $container->get('config');

        return $containerConfig[$configKey] ?? [];
    }

    /**
     * @param array $resolversStack
     */
    private function buildTemplateResolverFromStack(array $resolversStack): ?ResolverInterface
    {
        if (count($resolversStack) <= 1) {
            return array_shift($resolversStack);
        }

        // Attach all resolvers to the aggregate, if there's more than one
        $aggregateResolver = new AggregateResolver();
        foreach ($resolversStack as $resolver) {
            $aggregateResolver->attach($resolver);
        }
        return $aggregateResolver;
    }
}
