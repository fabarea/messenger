<?php

declare(strict_types=1);

namespace Fab\Messenger\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service for handling conditional backend module loading
 */
class BackendModuleService
{
    /**
     * Get the extension configuration
     */
    public function getConfiguration(): array
    {
        try {
            return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('messenger') ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check if a specific module should be loaded
     */
    public function shouldLoadModule(string $moduleKey): bool
    {
        $configuration = $this->getConfiguration();
        
        return match ($moduleKey) {
            'message_template' => (bool) ($configuration['load_message_template_module'] ?? false),
            'message_layout' => (bool) ($configuration['load_message_layout_module'] ?? false),
            'message_sent' => (bool) ($configuration['load_message_sent_module'] ?? true),
            'message_queue' => (bool) ($configuration['load_message_queue_module'] ?? true),
            'newsletter' => (bool) ($configuration['load_newsletter_module'] ?? true),
            default => true,
        };
    }

    /**
     * Get list of modules that should be hidden based on configuration
     */
    public function getHiddenModules(): array
    {
        $hiddenModules = [];
        
        if (!$this->shouldLoadModule('message_template')) {
            $hiddenModules[] = 'messenger_tx_messenger_m2';
        }
        
        if (!$this->shouldLoadModule('message_layout')) {
            $hiddenModules[] = 'messenger_tx_messenger_m3';
        }
        
        if (!$this->shouldLoadModule('message_sent')) {
            $hiddenModules[] = 'messenger_tx_messenger_m1';
        }
        
        if (!$this->shouldLoadModule('message_queue')) {
            $hiddenModules[] = 'messenger_tx_messenger_m4';
        }
        
        if (!$this->shouldLoadModule('newsletter')) {
            $hiddenModules[] = 'web_tx_messenger_m5';
        }
        
        return $hiddenModules;
    }
}
