<?php
/**
 * Markdown -> HTML renderer for WebPublisherSystem.
 *
 * Goals:
 *   - Always HTML-escape input. Inline emphasis is applied AFTER escaping.
 *   - Support what we use today: ATX headings (#..######), paragraphs,
 *     bullet/ordered lists, fenced code, blockquotes, horizontal rules,
 *     images, links, bold, italic, inline code, and explicit line breaks.
 *   - Be small enough to swap for a real CommonMark library later without
 *     callers changing.
 *
 * The single public entry point is wps_render_markdown($markdown).
 */

if (!function_exists('wps_render_markdown')) {

    function wps_render_markdown(string $markdown): string
    {
        $renderer = new WpsMarkdownRenderer();
        return $renderer->render($markdown);
    }

    final class WpsMarkdownRenderer
    {
        public function render(string $markdown): string
        {
            $lines = preg_split('/\r\n|\r|\n/', $markdown) ?: [];
            $html = '';
            $paragraph = [];
            $listType = null; // 'ul' or 'ol'
            $inBlockquote = false;
            $inFence = false;
            $fenceBuffer = [];
            $fenceLang = '';

            $flushParagraph = function () use (&$html, &$paragraph): void {
                if (!$paragraph) {
                    return;
                }
                $text = implode("\n", $paragraph);
                $html .= '<p>' . $this->renderInline($text) . '</p>' . "\n";
                $paragraph = [];
            };

            $closeList = function () use (&$html, &$listType): void {
                if ($listType !== null) {
                    $html .= '</' . $listType . '>' . "\n";
                    $listType = null;
                }
            };

            $closeBlockquote = function () use (&$html, &$inBlockquote): void {
                if ($inBlockquote) {
                    $html .= "</blockquote>\n";
                    $inBlockquote = false;
                }
            };

            foreach ($lines as $line) {
                if ($inFence) {
                    if (preg_match('/^\s*```\s*$/', $line)) {
                        $code = htmlspecialchars(implode("\n", $fenceBuffer), ENT_QUOTES, 'UTF-8');
                        $langAttr = $fenceLang !== '' ? ' class="language-' . htmlspecialchars($fenceLang, ENT_QUOTES, 'UTF-8') . '"' : '';
                        $html .= '<pre><code' . $langAttr . '>' . $code . "</code></pre>\n";
                        $inFence = false;
                        $fenceBuffer = [];
                        $fenceLang = '';
                        continue;
                    }
                    $fenceBuffer[] = $line;
                    continue;
                }

                if (preg_match('/^\s*```\s*([A-Za-z0-9_-]*)\s*$/', $line, $m)) {
                    $flushParagraph();
                    $closeList();
                    $closeBlockquote();
                    $inFence = true;
                    $fenceLang = (string) ($m[1] ?? '');
                    continue;
                }

                $trim = trim($line);

                if ($trim === '') {
                    $flushParagraph();
                    $closeList();
                    $closeBlockquote();
                    continue;
                }

                if (preg_match('/^(?:-{3,}|\*{3,}|_{3,})$/', $trim)) {
                    $flushParagraph();
                    $closeList();
                    $closeBlockquote();
                    $html .= "<hr>\n";
                    continue;
                }

                if (preg_match('/^(#{1,6})\s+(.+?)\s*#*\s*$/', $trim, $m)) {
                    $flushParagraph();
                    $closeList();
                    $closeBlockquote();
                    $level = strlen($m[1]);
                    $html .= '<h' . $level . '>' . $this->renderInline($m[2]) . '</h' . $level . '>' . "\n";
                    continue;
                }

                if (preg_match('/^>\s?(.*)$/', $trim, $m)) {
                    $flushParagraph();
                    $closeList();
                    if (!$inBlockquote) {
                        $html .= "<blockquote>\n";
                        $inBlockquote = true;
                    }
                    $html .= '<p>' . $this->renderInline($m[1]) . "</p>\n";
                    continue;
                }

                if (preg_match('/^[-*]\s+(.+)$/', $trim, $m)) {
                    $flushParagraph();
                    $closeBlockquote();
                    if ($listType !== 'ul') {
                        $closeList();
                        $html .= "<ul>\n";
                        $listType = 'ul';
                    }
                    $html .= '<li>' . $this->renderInline($m[1]) . "</li>\n";
                    continue;
                }

                if (preg_match('/^\d+[.)]\s+(.+)$/', $trim, $m)) {
                    $flushParagraph();
                    $closeBlockquote();
                    if ($listType !== 'ol') {
                        $closeList();
                        $html .= "<ol>\n";
                        $listType = 'ol';
                    }
                    $html .= '<li>' . $this->renderInline($m[1]) . "</li>\n";
                    continue;
                }

                $closeList();
                $closeBlockquote();
                $paragraph[] = $trim;
            }

            // Flush any unterminated state at EOF.
            if ($inFence) {
                $code = htmlspecialchars(implode("\n", $fenceBuffer), ENT_QUOTES, 'UTF-8');
                $html .= "<pre><code>" . $code . "</code></pre>\n";
            }
            $flushParagraph();
            $closeList();
            $closeBlockquote();

            return $html;
        }

        /**
         * Inline markdown: images, links, bold, italic, inline code.
         * Always escape first, then apply inline formatting via tokens
         * that survive escaping (no raw `<` in the input gets through).
         */
        private function renderInline(string $text): string
        {
            // Pull out inline code spans first so we don't apply emphasis
            // inside them.
            $codeSpans = [];
            $text = preg_replace_callback('/`([^`]+)`/', function ($m) use (&$codeSpans) {
                $codeSpans[] = '<code>' . htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8') . '</code>';
                return "\x01CODE" . (count($codeSpans) - 1) . "\x01";
            }, $text);

            // Escape everything else.
            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

            // Images: ![alt](url)
            $text = preg_replace_callback(
                '/!\[([^\]]*)\]\(([^)\s]+)(?:\s+&quot;([^&]*)&quot;)?\)/',
                function ($m) {
                    $alt = $m[1];
                    $url = $m[2];
                    $title = isset($m[3]) ? ' title="' . $m[3] . '"' : '';
                    return '<img src="' . $url . '" alt="' . $alt . '"' . $title . '>';
                },
                $text
            );

            // Links: [text](url)
            $text = preg_replace_callback(
                '/\[([^\]]+)\]\(([^)\s]+)\)/',
                function ($m) {
                    return '<a href="' . $m[2] . '">' . $m[1] . '</a>';
                },
                $text
            );

            // Bold: **text** or __text__
            $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
            $text = preg_replace('/__([^_]+)__/', '<strong>$1</strong>', $text);

            // Italic: *text* or _text_ (single, not part of bold)
            $text = preg_replace('/(?<!\*)\*([^*\s][^*]*[^*\s]|[^*\s])\*(?!\*)/', '<em>$1</em>', $text);
            $text = preg_replace('/(?<!_)_([^_\s][^_]*[^_\s]|[^_\s])_(?!_)/', '<em>$1</em>', $text);

            // Restore code spans.
            $text = preg_replace_callback("/\x01CODE(\d+)\x01/", function ($m) use ($codeSpans) {
                return $codeSpans[(int) $m[1]] ?? '';
            }, $text);

            return $text;
        }
    }
}
