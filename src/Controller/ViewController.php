<?php

namespace Sherlockode\SyliusAdvancedContentPlugin\Controller;

use Sherlockode\AdvancedContentBundle\Scope\ScopeHandlerInterface;
use Sherlockode\SyliusAdvancedContentPlugin\View\ViewHandlerInterface;
use Sherlockode\SyliusAdvancedContentPlugin\Scope\ChannelLocaleScopeHandler;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ViewController extends AbstractController
{
    /**
     * @var ChannelLocaleScopeHandler
     */
    private $channelLocaleScopeHandler;

    /**
     * @var RepositoryInterface
     */
    private $pageRepository;

    /**
     * @var ViewHandlerInterface
     */
    private $viewHandler;

    /**
     * @param ScopeHandlerInterface $channelLocaleScopeHandler
     * @param RepositoryInterface   $pageRepository
     * @param ViewHandlerInterface  $viewHandler
     */
    public function __construct(
        ScopeHandlerInterface $channelLocaleScopeHandler,
        RepositoryInterface $pageRepository,
        ViewHandlerInterface $viewHandler
    ) {
        $this->channelLocaleScopeHandler = $channelLocaleScopeHandler;
        $this->pageRepository = $pageRepository;
        $this->viewHandler = $viewHandler;
    }

    /**
     * @param Request $request
     * @param string  $pageIdentifier
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function viewAction(
        Request $request,
        string $pageIdentifier
    ): Response {

        $scope = $this->channelLocaleScopeHandler->getCurrentScope();

        if (!$scope) {
            throw new NotFoundHttpException('Scope for current channel does not exists');
        }

        $page = $this->pageRepository->findOneByPageIdentifier($pageIdentifier, $scope);

        if (!$page) {
            throw new NotFoundHttpException('Page with scope for current channel and scope does not exists');
        }

        $template = $this->viewHandler->getViewTemplate($page, $scope);

        if (!$template) {
            throw new \Exception(sprintf(
                'Cannot find any template for the page "%s" preview',
                $page->getPageIdentifier()
            ));
        }

        return $this->render($template, ['page' => $page]);
    }
}
