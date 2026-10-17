<!-- Quill rich-text editor (MIT licensed, no API key needed) - shared by any
     admin form that edits HTML content (pages, posts, events, ...).

     Loaded with a CDN fallback chain (jsDelivr -> cdnjs -> unpkg) because a
     single blocked/unreachable CDN is the most common reason the editor area
     renders as an empty box: if one host is blocked by a network/firewall,
     the next is tried automatically. If every CDN fails (e.g. the machine
     has no internet access at all), the box falls back to a plain,
     fully-usable HTML textarea instead of staying blank. -->
<style>
    .ql-toolbar.ql-snow { border-color: #e5e7eb; border-radius: .5rem .5rem 0 0; background: #f9fafb; }
    .ql-container.ql-snow { border-color: #e5e7eb; border-radius: 0 0 .5rem .5rem; font-size: .95rem; }
    .ql-editor { min-height: 260px; }
    .ql-snow.ql-toolbar button:hover, .ql-snow .ql-toolbar button:hover,
    .ql-snow.ql-toolbar button.ql-active, .ql-snow .ql-toolbar button.ql-active { color: #1f5c3d; }
    .ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke: #1f5c3d; }
    .ql-snow.ql-toolbar button:hover .ql-fill, .ql-snow.ql-toolbar button.ql-active .ql-fill { fill: #1f5c3d; }
    .rich-editor-fallback-notice {
        display: flex; align-items: center; gap: .5rem; font-size: .8rem; color: #B2590F;
        background: #FBEEE3; border: 1px solid #F6D9BE; border-radius: .5rem; padding: .6rem .75rem; margin-bottom: .5rem;
    }
    .rich-editor-fallback-textarea {
        width: 100%; min-height: 260px; border: 1px solid #e5e7eb; border-radius: .5rem;
        padding: .75rem; font-family: ui-monospace, monospace; font-size: .85rem; line-height: 1.5;
    }
</style>
<script>
    /**
     * Loads Quill's CSS/JS from the first CDN that responds, trying each
     * host in turn. Resolves once Quill is available, or rejects if none of
     * the hosts could be reached.
     */
    window.__loadQuill = window.__loadQuill || function () {
        if (window.__quillLoadPromise) {
            return window.__quillLoadPromise;
        }

        var cssHosts = [
            'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css',
            'https://cdnjs.cloudflare.com/ajax/libs/quill/2.0.2/quill.snow.min.css',
            'https://unpkg.com/quill@2.0.2/dist/quill.snow.css'
        ];
        var jsHosts = [
            'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.umd.js',
            'https://cdnjs.cloudflare.com/ajax/libs/quill/2.0.2/quill.min.js',
            'https://unpkg.com/quill@2.0.2/dist/quill.umd.js'
        ];

        cssHosts.forEach(function (href) {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            document.head.appendChild(link);
        });

        function tryScript(index) {
            return new Promise(function (resolve, reject) {
                if (typeof Quill !== 'undefined') {
                    resolve();
                    return;
                }
                if (index >= jsHosts.length) {
                    reject(new Error('All Quill CDN hosts failed to load.'));
                    return;
                }
                var script = document.createElement('script');
                script.src = jsHosts[index];
                script.onload = function () { resolve(); };
                script.onerror = function () {
                    script.remove();
                    tryScript(index + 1).then(resolve, reject);
                };
                document.head.appendChild(script);
            });
        }

        window.__quillLoadPromise = tryScript(0);
        return window.__quillLoadPromise;
    };

    /**
     * Wires a Quill editor to a hidden textarea so the existing form
     * submission (and server-side validation) doesn't need to change at all -
     * the textarea's value is kept in sync with the editor's HTML output.
     * Falls back to a plain visible textarea if Quill can't be loaded at all,
     * so the field is never just a blank, unusable box.
     */
    function initRichEditor(editorElementId, hiddenFieldId) {
        var mount  = document.getElementById(editorElementId);
        var hidden = document.getElementById(hiddenFieldId);
        if (!mount || !hidden) {
            return;
        }

        window.__loadQuill().then(function () {
            var quill = new Quill('#' + editorElementId, {
                theme: 'snow',
                placeholder: 'Write the content here...',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ color: [] }, { background: [] }],
                        [{ list: 'ordered' }, { list: 'bullet' }, { indent: '-1' }, { indent: '+1' }],
                        [{ align: [] }],
                        ['blockquote', 'code-block', 'link', 'image'],
                        ['clean']
                    ]
                }
            });

            quill.root.innerHTML = hidden.value;
            quill.on('text-change', function () { hidden.value = quill.root.innerHTML; });

            var form = hidden.closest('form');
            if (form) {
                form.addEventListener('submit', function () { hidden.value = quill.root.innerHTML; });
            }
        }).catch(function () {
            // No CDN reachable - fall back to a plain, visible HTML textarea
            // so content can still be written and saved.
            var notice = document.createElement('div');
            notice.className = 'rich-editor-fallback-notice';
            notice.innerHTML = '<span>&#9888;&#65039;</span><span>The rich-text editor could not be loaded (no connection to its CDN). You can still write HTML directly below.</span>';
            mount.replaceWith(notice);
            notice.insertAdjacentElement('afterend', hidden);
            hidden.classList.remove('hidden');
            hidden.classList.add('rich-editor-fallback-textarea');
        });
    }
</script>
