/**
 * エントリーポイント
 * コンポーネント登録 → 自動マウント
 */
import './app';
import { registerComponent } from './app';
import Counter from './components/Counter.vue';

// コンポーネント登録
registerComponent('Counter', Counter);
