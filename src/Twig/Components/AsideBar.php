<?php

namespace App\Twig\Components;
use Symfony\Component\Security\Http\Attribute\IsGranted;


use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[IsGranted('ROLE_USER')]
#[AsTwigComponent]
final class AsideBar
{
}
