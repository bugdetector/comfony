import { startStimulusApp } from '@symfony/stimulus-bridge';
import AutosubmitController from './controllers/autosubmit_controller';
import TinyMceController from './controllers/tinymce_controller';
import FlashMessageController from './controllers/flash_message_controller';

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));
// register any custom, 3rd party controllers here
app.register('autosubmit', AutosubmitController);
app.register('tinymce', TinyMceController);
app.register('flashmessage', FlashMessageController)
