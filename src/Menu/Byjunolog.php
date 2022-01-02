<?php

namespace Ij\SyliusByjunoPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class Byjunolog
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $newSubmenu = $menu
            ->addChild('new')
            ->setLabel('Byjuno')
        ;

        $newSubmenu
            ->addChild('new-subitem', [
                'route' => 'app_admin_byjunolog_route'
            ])
            ->setLabel('Byjuno logs')
        ;
    }
}
