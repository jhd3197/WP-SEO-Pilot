import { render } from '@wordpress/element';
import App from './App';

import './index.css';

const initialView = window?.samanlabsSeoSettings?.initialView || 'dashboard';

render(<App initialView={initialView} />, document.getElementById('samanlabs-seo-v2-root'));
