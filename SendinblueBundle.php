<?php

namespace RevisionTen\Sendinblue;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SendinblueBundle extends Bundle
{
    public const VERSION = '1.1.1';

    public function boot(): void
    {
    }

    public function build(ContainerBuilder $container): void
    {
    }
}
