/**
 * WP SEO Pilot - Visual Tags & Rich Input
 */
(function ($, settings) {
    const initRichTags = () => {
        console.log('WP SEO Pilot: Initializing Rich Tags...');
        const tagRegex = /\{\{\s*([^}]+)\s*\}\}/g; // Matches {{variable}} or {{ variable }}

        const vars = [
            'post_title',
            'site_title',
            'tagline',
            'post_author',
            'separator',
            'date',
            'current_year',
            'current_month',
            'current_day',
            'modified',
            'category',
        ];

        let activeEditor = null;
        const $menu = $('<div class="wpseopilot-autocomplete-menu"></div>').appendTo('body');
        let selectedIndex = 0;

        const hideMenu = () => {
            $menu.removeClass('is-visible').empty();
            activeEditor = null;
            selectedIndex = 0;
        };

        const insertVar = (variable) => {
            console.log('WP SEO Pilot: Inserting variable', variable);
            const editor = activeEditor; // Cache it because hideMenu clears it

            if (!editor) {
                console.error('WP SEO Pilot: No active editor found for insertion');
                return;
            }

            // Restore focus to editor if lost
            editor.focus();

            const sel = window.getSelection();
            if (sel.rangeCount) {
                const range = sel.getRangeAt(0);

                // Ensure range is inside the editor
                if (!editor.contains(range.startContainer)) {
                    console.warn('WP SEO Pilot: Selection is outside editor, appending to end');
                    // Move caret to end
                    range.selectNodeContents(editor);
                    range.collapse(false);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }

                const tagHtml = `<span class="wpseopilot-tag" contenteditable="false">{{${variable}}}</span>&nbsp;`;

                const textNode = range.startContainer;
                if (textNode.nodeType === 3) {
                    const text = textNode.textContent;
                    const offset = range.startOffset;
                    // Look for '{{' or '{' just before cursor
                    let triggerLen = 0;
                    if (offset >= 2 && text.substring(offset - 2, offset) === '{{') {
                        triggerLen = 2;
                    }

                    if (triggerLen > 0) {
                        console.log('WP SEO Pilot: Removing trigger characters');
                        const before = text.substring(0, offset - triggerLen);
                        const after = text.substring(offset);
                        textNode.textContent = before + after;

                        const newRange = document.createRange();
                        newRange.setStart(textNode, offset - triggerLen);
                        newRange.setEnd(textNode, offset - triggerLen);
                        sel.removeAllRanges();
                        sel.addRange(newRange);
                    }
                }

                document.execCommand('insertHTML', false, tagHtml);
            } else {
                console.log('WP SEO Pilot: No range, appending to end');
                $(editor).append(`<span class="wpseopilot-tag" contenteditable="false">{{${variable}}}</span>&nbsp;`);
            }

            hideMenu();
            $(editor).trigger('input'); // Sync
            $(editor).focus();
        };

        const renderMenu = (matches) => {
            $menu.empty();
            if (!matches.length) {
                hideMenu();
                return;
            }

            matches.forEach((match, index) => {
                const $item = $('<div class="wpseopilot-autocomplete-item"></div>')
                    .text(match)
                    .attr('data-value', match);

                if (index === selectedIndex) {
                    $item.addClass('is-selected');
                }

                $item.on('mousedown', (e) => {
                    // Important: mousedown prevents the editor from losing focus before we can insert
                    e.preventDefault();
                    e.stopPropagation();
                    insertVar(match);
                });
                $menu.append($item);
            });

            if (activeEditor) {
                const offset = $(activeEditor).offset();
                console.log('WP SEO Pilot: Opening menu at', offset);
                $menu.css({
                    top: offset.top + $(activeEditor).outerHeight(),
                    left: offset.left,
                    width: $(activeEditor).outerWidth()
                }).addClass('is-visible');
            }
        };

        const createRichInput = (input) => {
            const $input = $(input);
            if ($input.data('rich-init')) return;
            $input.data('rich-init', true);
            console.log('WP SEO Pilot: Creating rich input for', input.id || input.name);

            const $container = $('<div class="wpseopilot-rich-container"></div>');
            const $editor = $('<div class="wpseopilot-rich-input" contenteditable="true"></div>');

            if ($input.is('textarea')) {
                $editor.addClass('is-textarea');
            }

            $input.hide().after($container);
            $container.append($editor);

            // Expose editor for external triggers
            $input.data('editor', $editor);

            const syncToEditor = () => {
                let text = $input.val() || '';
                // render pills
                const html = text.replace(tagRegex, '<span class="wpseopilot-tag" contenteditable="false">{{$1}}</span>');
                $editor.html(html);
            };

            const syncToInput = () => {
                let text = $editor[0].innerText;
                // remove Zero Width Spaces if any seem to creep in
                $input.val(text).trigger('change');
            };

            syncToEditor();

            $editor.on('input', function () {
                syncToInput();

                const sel = window.getSelection();
                if (!sel.rangeCount) return;
                const range = sel.getRangeAt(0);
                const node = range.startContainer;

                if (node.nodeType === 3) {
                    const text = node.textContent;
                    const caret = range.startOffset;

                    // Detect '{{'
                    const lastOpen = text.lastIndexOf('{{', caret);
                    if (lastOpen !== -1) {
                        const query = text.substring(lastOpen + 2, caret).trim();
                        if (!query.includes('}}')) {
                            activeEditor = $editor[0];
                            const matches = vars.filter(v => v.startsWith(query));
                            selectedIndex = 0;
                            renderMenu(matches);
                            return;
                        }
                    }
                }
                hideMenu();
            });

            $editor.on('keydown', function (e) {
                if ($menu.hasClass('is-visible')) {
                    const items = $menu.children();
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        selectedIndex = (selectedIndex + 1) % items.length;
                        items.removeClass('is-selected');
                        items.eq(selectedIndex).addClass('is-selected');
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                        items.removeClass('is-selected');
                        items.eq(selectedIndex).addClass('is-selected');
                    } else if (e.key === 'Enter' || e.key === 'Tab') {
                        e.preventDefault();
                        const val = items.eq(selectedIndex).data('value');
                        if (val) insertVar(val);
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        hideMenu();
                    }
                }
            });

            // Click outside to close
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.wpseopilot-autocomplete-menu').length && !$(e.target).closest('.wpseopilot-trigger-vars').length) {
                    hideMenu();
                }
            });
        };

        // Initialize on existing inputs
        $('input[name^="wpseopilot_"], textarea[name^="wpseopilot_"]').each(function () {
            if ($(this).attr('type') !== 'checkbox' && $(this).attr('type') !== 'radio') {
                createRichInput(this);
            }
        });

        // Trigger Button Logic
        $(document).on('click', '.wpseopilot-trigger-vars', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const targetId = $(this).data('target');
            console.log('WP SEO Pilot: Trigger button clicked for', targetId);
            const $input = $('#' + targetId);
            const $editor = $input.data('editor');

            if ($editor) {
                $editor.focus();
                activeEditor = $editor[0];

                // Calculate position
                const offset = $editor.offset();
                $menu.css({
                    top: offset.top + $editor.outerHeight(),
                    left: offset.left,
                    width: $editor.outerWidth()
                }).addClass('is-visible');

                selectedIndex = 0;
                renderMenu(vars);
            } else {
                console.error('WP SEO Pilot: No editor found for input', targetId);
            }
        });
    };

    $(document).ready(function () {
        initRichTags();
    });

})(jQuery, window.WPSEOPilotAdmin);
