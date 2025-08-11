import { Controller } from "@hotwired/stimulus"
import tinymce from 'tinymce';
import 'tinymce/themes/silver';
import 'tinymce/plugins/autolink'
import 'tinymce/plugins/image'

// Connects to data-controller="tinymce"
export default class TinyMceController extends Controller {
  connect() {
    var useDarkMode = localStorage.getItem('theme') === 'dracula' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
    const config = {
      target: this.element,
      plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons accordion',
      editimage_cors_hosts: ['picsum.photos'],
      menubar: 'file edit view insert format tools table help',
      toolbar: "undo redo | accordion accordionremove | blocks fontfamily fontsize | bold italic underline strikethrough | align numlist bullist | link image table media blockquote | lineheight outdent indent| forecolor backcolor removeformat | charmap emoticons | code visualblocks fullscreen preview | save print | pagebreak anchor codesample | ltr rtl",
      autosave_ask_before_unload: true,
      autosave_interval: '30s',
      autosave_prefix: '{path}{query}-{id}-',
      autosave_restore_when_empty: false,
      autosave_retention: '2m',
      image_advtab: true,
      importcss_append: true,
      template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
      template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
      height: 600,
      image_caption: true,
      quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
      noneditable_class: 'mceNonEditable',
      toolbar_mode: 'wrap',
      contextmenu: 'copy paste link image table',
      skin: useDarkMode ? 'oxide-dark' : 'oxide',
      content_css: useDarkMode ? 'dark' : 'default',
      content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
      branding: false,
      promotion: false,
      valid_elements : '*[*]',
      init_instance_callback: function(editor) {
        editor.shortcuts.remove('alt+0');
      },
      setup: function (editor) {
        editor.on('change keyup input', () => {
          editor.save();
          editor.getElement().dispatchEvent(new Event('change', {bubbles: true}))
        });
      }
    };
    tinymce.init(config);
    document.querySelectorAll('input[name="theme"]').forEach((element) => {
      element.addEventListener('click', () => {
        setTimeout(() => {
          useDarkMode = localStorage.getItem('theme')  === 'dracula' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
          config.skin = useDarkMode ? 'oxide-dark' : 'oxide';
          config.content_css = useDarkMode ? 'dark' : 'default';
          tinymce.remove();
          tinymce.init(config);
        })
      })
    })
  }

  disconnect() {
    tinymce.remove();
  }
}