<?php

namespace BO\Zmsadmin\Helper;

class ChangelogHelper
{
    private const GITHUB_API_URL = 'https://api.github.com/repos/it-at-m/eappointment/contents/CHANGELOG.md';

    /**
     * Fetch changelog content from GitHub and convert to HTML
     *
     * @return string HTML content of the changelog
     */
    public function getChangelogHtml(): string
    {
        $cacheFile = sys_get_temp_dir() . '/eappointment_changelog_cache.json';

        // Check cache first
        if (file_exists($cacheFile)) {
            $cache = json_decode(file_get_contents($cacheFile), true);
            if ($cache && isset($cache['content'])) {
                return $cache['content'];
            }
        }

        try {
            $markdown = $this->fetchChangelogFromGitHub();
            $html = $this->convertMarkdownToHtml($markdown);

            // Cache the result
            file_put_contents($cacheFile, json_encode([
                'content' => $html
            ]));

            return $html;
        } catch (\Exception $e) {
            // Return empty string or fallback content on error
            error_log('Failed to fetch changelog: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Fetch CHANGELOG.md content from GitHub API
     *
     * @return string
     * @throws \Exception
     */
    private function fetchChangelogFromGitHub(): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => self::GITHUB_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'eappointment-changelog-fetcher',
            CURLOPT_HTTPHEADER => [
                'Accept: application/vnd.github.v3+json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('cURL error: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new \Exception('HTTP error: ' . $httpCode);
        }

        $data = json_decode($response, true);

        if (!isset($data['content']) || !isset($data['encoding'])) {
            throw new \Exception('Invalid response from GitHub API');
        }

        if ($data['encoding'] !== 'base64') {
            throw new \Exception('Unexpected encoding from GitHub API');
        }

        return base64_decode($data['content']);
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
            }
            // Handle lists
            elseif (preg_match('/^- (.+)$/', $line, $matches)) {
                if (!$inList) {
                    $html .= '<ul>' . "\n";
                    $inList = true;
                }
                $html .= '<li>' . $this->processInlineMarkdown($matches[1]) . '</li>' . "\n";
            }
            // Handle horizontal rule
            elseif (preg_match('/^---$/', $line)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= '<hr />' . "\n";
            }
            // Handle regular text
            else {
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
