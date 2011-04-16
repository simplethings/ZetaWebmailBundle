<?php
/*
 * Copyright 2011 SimpleThings GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace SimpleThings\ZetaWebmailBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;

class SimpleThingsZetaWebmailExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config/"));
        $loader->load("services.yml");

        $security = null;
        foreach ($configs AS $config) {
            if (isset($config['security'])) {
                $security = $config['security'];
            }

            if (isset($config['list_layout'])) {
                $container->setParameter('simplethings.zetawebmail.listlayout', $config['list_layout']);
            }

            if (isset($config['sources']) && is_array($config['sources'])) {
                foreach ($config['sources'] AS $sourceName => $sourceConfig) {
                    if (isset($sourceConfig['type'])) {
                        switch($sourceConfig['type']) {
                            case 'imap':
                                $def = new Definition('SimpleThings\ZetaWebmailBundle\Mailbox\Loader\ImapLoader', array(
                                    $sourceName, $sourceConfig['host'], $sourceConfig['username'], $sourceConfig['password'],
                                    (isset($sourceConfig['port'])) ? $sourceConfig['port'] : null,
                                    (isset($sourceConfig['ssl'])) ? $sourceConfig['ssl'] : true,
                                    (isset($sourceConfig['timeout'])) ? $sourceConfig['timeout'] : 2
                                ));
                                $container->setDefinition('simplethings.zetawebmail.loader.'.$sourceName, $def);
                                break;
                            case 'pop':
                                $def = new Definition('SimpleThings\ZetaWebmailBundle\Mailbox\Loader\PopLoader', array(
                                    $sourceName, $sourceConfig['host'], $sourceConfig['username'], $sourceConfig['password'],
                                    (isset($sourceConfig['port'])) ? $sourceConfig['port'] : null,
                                    (isset($sourceConfig['ssl'])) ? $sourceConfig['ssl'] : true,
                                    (isset($sourceConfig['timeout'])) ? $sourceConfig['timeout'] : 2
                                ));
                                $container->setDefinition('simplethings.zetawebmail.loader.'.$sourceName, $def);
                                break;
                            default:
                                $container->setAlias('simplethings.zetawebmail.loader.'.$sourceName, $sourceConfig['type']);
                                break;
                        }
                    }
                }
            }
        }

        if (in_array($security, array("admin_party", "zeta_mail_role"))) {
            $container->setAlias('simplethings.zetawebmail.security', 'simplethings.zetawebmail.security.'.$security);
        } else if (strlen($security)) {
            $container->setAlias('simplethings.zetawebmail.security', $security);
        } else {
            throw new \InvalidArgumentException("Missing security attribute in simple_things_zeta_webmail extension.");
        }
    }
}