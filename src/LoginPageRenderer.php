<?php

declare(strict_types=1);

namespace Capsule;

use App\Http\Dev\Sections\SectionDefaults;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Response;
use Capsule\Section\Signup\SignupStyle;

/**
 * Page publique /login : affiche le bloc de connexion actif de l'en-tête.
 */
final class LoginPageRenderer
{
    public function __construct(
        private readonly ResponseFactory $responseFactory,
        private readonly View $view,
        private readonly SiteRepository $site,
        private readonly AuthBlockRenderer $authBlocks,
        private readonly SiteChrome $chrome,
        private readonly string $baseUrl,
        private readonly StylesheetResolver $stylesheets,
        private readonly ScriptResolver $scripts,
        private readonly string $publicCssDir,
        private readonly ?BasePath $basePath = null,
    ) {
    }

    public function render(string $path = '/login'): Response
    {
        $siteInfo = LoginBlockLibrary::materialize($this->site->getSite());
        $block = LoginBlockResolver::resolveActiveHeaderBlock($siteInfo);
        if ($block === null) {
            $blocks = LoginBlockLibrary::blocks($siteInfo);
            $block = $blocks[0] ?? null;
        }

        if ($block === null) {
            $block = [
                'id' => 'login-fallback',
                'variant' => 'login1',
                'content' => [],
                'style' => [],
            ];
        }

        $body = $this->authBlocks->renderPair($block, $siteInfo);
        $sectionRefs = $this->authBlocks->sectionRefs($block, $siteInfo);
        $loginSection = LoginBlockLibrary::toSection($block, $siteInfo);
        $signupVariant = LoginBlockLibrary::pairedSignupVariant((string) ($block['variant'] ?? 'login1'));
        $signupSection = LoginBlockLibrary::toSignupSection([
            'id' => (string) ($block['id'] ?? 'signup') . '-signup',
            'variant' => $signupVariant,
            'content' => SectionDefaults::content('signup', $signupVariant),
            'style' => SignupStyle::defaults($signupVariant),
        ], $siteInfo);
        $sections = [$loginSection, $signupSection];

        $data = [
            'title' => 'Connexion - ' . trim((string) ($siteInfo['name'] ?? 'Site')),
            'description' => 'Connectez-vous à votre espace.',
            'layout' => 'default',
        ];
        $data['theme'] = $this->site->getTheme();
        $data['asset_root'] = $this->basePath?->value() ?? '';
        $this->site->ensureThemeCssFile($this->publicCssDir);
        $data['theme_css'] = $this->site->themeHeadHtml($data['asset_root'], $theme, $this->publicCssDir);
        $data = Seo::apply($data, $path, $this->baseUrl);
        $data = $this->chrome->enrich($data, $path);

        $hrefs = $this->stylesheets->resolve('default', 'login', $body, $data, $sectionRefs, $sections);
        $data['stylesheets'] = $this->stylesheets->toHtml($hrefs, $data['asset_root']);

        $scriptSrcs = $this->scripts->resolve(
            $body . ($data['header_html'] ?? '') . ($data['login_modal_html'] ?? ''),
            $sectionRefs,
        );
        $data['scripts'] = $this->scripts->toHtml($scriptSrcs, $data['asset_root']);

        $html = $this->view->pageFromString($body, $data, 'default.html');

        return $this->responseFactory->html($html);
    }
}
