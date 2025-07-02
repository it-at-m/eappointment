<?php

namespace BO\Zmsstatistic\Helper;

class ChangelogHelper
{
    /**
     * Fetch changelog content from local file and convert to HTML
     *
     * @return string HTML content of the changelog
     */
    public function getChangelogHtml(): string
    {
        try {
            $markdown = $this->fetchChangelogFromLocal();
            $html = $this->convertMarkdownToHtml($markdown);
            return $html;
        } catch (\Exception $e) {
            error_log('Failed to fetch changelog: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Fetch CHANGELOG.md content from local file
     *
     * @return string
     * @throws \Exception
     */
    private function fetchChangelogFromLocal(): string
    {
        $localFile = __DIR__ . '/../../../changelog_build.md';
        if (!file_exists($localFile)) {
            throw new \Exception('Local changelog file not found: ' . $localFile);
        }
        error_log('ChangelogHelper: __DIR__ is ' . __DIR__);
        return file_get_contents($localFile);
    }

    /**
     * Convert markdown to HTML while preserving existing HTML structure
     *
     * @param string $markdown
     * @return string
     */
    private function convertMarkdownToHtml(string $markdown): string
    {
        $html = '';
        $lines = explode("\n", $markdown);
        $inList = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= "\n";
                continue;
            }

            // Handle headers
            if (preg_match('/^### (.+)$/', $line, $matches)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= '<h3>' . $this->escapeHtml($matches[1]) . '</h3>' . "\n";
            } elseif (preg_match('/^## (.+)$/', $line, $matches)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= '<h2>' . $this->escapeHtml($matches[1]) . '</h2>' . "\n";
            } elseif (preg_match('/^# (.+)$/', $line, $matches)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= '<h1>' . $this->escapeHtml($matches[1]) . '</h1>' . "\n";
            } elseif (preg_match('/^- (.+)$/', $line, $matches)) {
                if (!$inList) {
                    $html .= '<ul>' . "\n";
                    $inList = true;
                }
                $html .= '<li>' . $this->processInlineMarkdown($matches[1]) . '</li>' . "\n";
            } elseif (preg_match('/^---$/', $line)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= '<hr />' . "\n";
            } else {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= $this->processInlineMarkdown($line) . "\n";
            }
        }

        // Close any open list
        if ($inList) {
            $html .= "</ul>\n";
        }

        return $html;
    }

    /**
     * Process inline markdown elements like links and formatting
     *
     * @param string $text
     * @return string
     */
    private function processInlineMarkdown(string $text): string
    {
        // Convert markdown links to HTML links
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);

        // Convert bold text
        $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);

        // Convert italic text
        $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);

        // Convert inline code
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);

        return $text;
    }

    /**
     * Escape HTML special characters
     *
     * @param string $text
     * @return string
     */
    private function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
