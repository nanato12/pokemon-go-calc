import { createApp, type Component } from 'vue';

// コンポーネントレジストリ
const components: Record<string, Component> = {};

/**
 * コンポーネントを登録
 */
export function registerComponent(name: string, component: Component): void {
    components[name] = component;
}

/**
 * data-vue-component 属性を持つ要素にVueコンポーネントをマウント
 * Islands Architecture パターン
 */
export function mountComponents(): void {
    const elements = document.querySelectorAll<HTMLElement>('[data-vue-component]');

    elements.forEach((el) => {
        const componentName = el.dataset.vueComponent;

        if (!componentName) {
            return;
        }

        const component = components[componentName];

        if (!component) {
            console.warn(`Component "${componentName}" not found`);

            return;
        }

        // data-* 属性からpropsを取得
        const props: Record<string, unknown> = {};

        for (const key in el.dataset) {
            if (key !== 'vueComponent') {
                // data-job-id → jobId に変換済み（dataset APIの仕様）
                props[key] = parseDataValue(el.dataset[key]);
            }
        }

        createApp(component, props).mount(el);
    });
}

/**
 * data属性の値をパース
 */
function parseDataValue(value: string | undefined): unknown {
    if (value === undefined) {
        return undefined;
    }

    if (value === 'true') {
        return true;
    }

    if (value === 'false') {
        return false;
    }

    if (value === 'null') {
        return null;
    }

    const num = Number(value);

    if (!isNaN(num) && value.trim() !== '') {
        return num;
    }

    // JSON配列/オブジェクトの場合
    if ((value.startsWith('[') && value.endsWith(']')) || (value.startsWith('{') && value.endsWith('}'))) {
        try {
            return JSON.parse(value);
        } catch {
            return value;
        }
    }

    return value;
}

// DOMContentLoaded で自動マウント
document.addEventListener('DOMContentLoaded', () => {
    mountComponents();
});
