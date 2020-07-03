<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Knp\Bundle\PaginatorBundle\KnpPaginatorBundle::class => ['all' => true],
    Roukmoute\HashidsBundle\RoukmouteHashidsBundle::class => ['all' => true],
    Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => ['all' => true],
    Danilovl\ParameterBundle\ParameterBundle::class => ['all' => true],
    Danilovl\RenderServiceTwigExtensionBundle\RenderServiceTwigExtensionBundle::class => ['all' => true],
    EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle::class => ['all' => true],
    Overblog\GraphiQLBundle\OverblogGraphiQLBundle::class => ['dev' => true],
    Overblog\GraphQLBundle\OverblogGraphQLBundle::class => ['all' => true],
    App\AppBundle::class => ['all' => true],
];
