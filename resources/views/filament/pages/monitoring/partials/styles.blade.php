<style>
    .mon {
        --bg: #ffffff;
        --soft: #f1f5f9;
        --line: #e2e8f0;
        --text: #0f172a;
        --muted: #475569;
        --primary: #0d9488;
    }

    .dark .mon {
        --bg: #18181b;
        --soft: #27272a;
        --line: #3f3f46;
        --text: #f4f4f5;
        --muted: #a1a1aa;
        --primary: #2dd4bf;
    }

    .mon {
        color: var(--text);
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .mon * {
        box-sizing: border-box;
    }

    /* Toolbar */
    .mon-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        align-items: flex-end;
    }

    .mon-field {
        display: flex;
        flex-direction: column;
        gap: .25rem;
    }

    .mon-field label {
        font-size: .75rem;
        color: var(--muted);
        padding-left: .5rem;
    }

    .mon-input,
    .mon-select {
        min-height: 2.75rem;
        padding: 0 1rem;
        border-radius: 9999px;
        border: 1px solid var(--line);
        background: var(--bg);
        color: var(--text);
        font-size: .9rem;
        outline: none;
        min-width: 11rem;
    }

    .mon-input {
        min-width: 16rem;
    }

    .mon-input:focus-visible,
    .mon-select:focus-visible,
    .mon-action-card:focus-visible {
        outline: 3px solid var(--primary);
        outline-offset: 2px;
    }

    .mon-live {
        margin-left: auto;
        font-size: .78rem;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .mon-dot {
        width: .5rem;
        height: .5rem;
        border-radius: 9999px;
        background: #22c55e;
        animation: mon-pulse 2s infinite;
    }

    @keyframes mon-pulse {

        0%,
        100% {
            opacity: 1
        }

        50% {
            opacity: .3
        }
    }

    /* Top: stat cards + donut */
    .mon-top {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    @media (min-width: 1024px) {
        .mon-top {
            grid-template-columns: 1.6fr 1fr;
        }
    }

    .mon-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .mon-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .mon-card {
        background: var(--bg);
        border: 1px solid var(--line);
        border-radius: 1.25rem;
        padding: 1.1rem 1.25rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
    }

    .mon-card::after {
        content: "";
        position: absolute;
        right: -2rem;
        bottom: -2rem;
        width: 6rem;
        height: 6rem;
        border-radius: 9999px;
        background: var(--accent, var(--soft));
        opacity: .12;
    }

    .mon-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: .75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--accent, #94a3b8) 15%, transparent);
        color: var(--accent, #94a3b8);
        margin-bottom: .6rem;
    }

    .mon-icon svg {
        width: 1.3rem;
        height: 1.3rem;
    }

    .mon-num {
        font-size: 1.9rem;
        font-weight: 700;
        line-height: 1;
    }

    .mon-lbl {
        font-size: .85rem;
        color: var(--muted);
        margin-top: .3rem;
    }

    .mon-card.alert {
        border-color: #d97706;
    }

    .dark .mon-card.alert {
        border-color: #fbbf24;
    }

    .mon-action-card {
        min-height: 44px;
        width: 100%;
        color: inherit;
        text-align: left;
        cursor: pointer;
    }

    .mon-action-card:hover {
        border-color: var(--primary);
    }

    .mon-action-card[aria-pressed="true"] {
        border-color: #b45309;
        box-shadow: 0 0 0 2px color-mix(in srgb, #b45309 20%, transparent);
    }

    /* Donut */
    .mon-donut-card {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .mon-donut {
        width: 9rem;
        height: 9rem;
        border-radius: 9999px;
        position: relative;
        flex-shrink: 0;
    }

    .mon-donut::after {
        content: "";
        position: absolute;
        inset: 1.9rem;
        background: var(--bg);
        border-radius: 9999px;
    }

    .mon-donut-center {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }

    .mon-donut-center b {
        font-size: 1.5rem;
    }

    .mon-donut-center span {
        font-size: .7rem;
        color: var(--muted);
    }

    .mon-legend {
        display: flex;
        flex-direction: column;
        gap: .4rem;
        font-size: .85rem;
    }

    .mon-legend div {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .mon-legend i {
        width: .7rem;
        height: .7rem;
        border-radius: 9999px;
        display: inline-block;
    }

    .mon-legend b {
        margin-left: auto;
        padding-left: 1rem;
    }

    /* Section header */
    .mon-head {
        display: flex;
        flex-direction: column;
        gap: .15rem;
    }

    .mon-head h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
    }

    .mon-head p {
        font-size: .8rem;
        color: var(--muted);
        margin: 0;
    }

    /* Session list */
    .mon-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .mon-item {
        display: flex;
        gap: 1.25rem;
        background: var(--soft);
        border-radius: 1.25rem;
        padding: 1rem;
        border: 1px solid transparent;
        transition: border-color .15s, transform .15s;
    }

    .mon-item:hover {
        border-color: var(--primary);
    }

    .mon-time {
        width: 6.5rem;
        flex-shrink: 0;
        border-radius: 1rem;
        background: var(--bg);
        border: 1px solid var(--line);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: .75rem .25rem;
        text-align: center;
        border-left: 5px solid var(--accent);
    }

    .mon-time b {
        font-size: 1.05rem;
    }

    .mon-time span {
        font-size: .72rem;
        color: var(--muted);
    }

    .mon-body {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: .55rem;
    }

    .mon-title {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .mon-title h4 {
        font-size: 1.02rem;
        font-weight: 600;
        margin: 0;
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: .01em;
    }

    .mon-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem 1.25rem;
        font-size: .85rem;
        color: var(--muted);
    }

    .mon-meta span {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .mon-meta svg {
        width: 1rem;
        height: 1rem;
    }

    .mon-foot {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .6rem;
        margin-top: .15rem;
    }

    .mon-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .8rem;
        border-radius: 9999px;
        font-size: .78rem;
        font-weight: 500;
        background: color-mix(in srgb, var(--accent) 14%, var(--bg));
        color: var(--accent-text);
    }

    .mon-badge svg {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }

    .mon-actions {
        margin-left: auto;
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .mon-actions button {
        min-height: 44px;
    }

    .mon-item-actionable {
        border-color: #b45309;
    }

    .dark .mon-item-actionable {
        border-color: #fbbf24;
    }

    .mon-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--muted);
        background: var(--soft);
        border-radius: 1.25rem;
    }

    @media (max-width: 640px) {
        .mon-item {
            flex-direction: column;
        }

        .mon-time {
            width: 100%;
            flex-direction: row;
            gap: .5rem;
            border-left-width: 1px;
            border-top: 5px solid var(--accent);
        }

        .mon-actions {
            margin-left: 0;
        }

        .mon-live {
            margin-left: 0;
        }

        .mon-input,
        .mon-select {
            width: 100%;
            min-width: 0;
        }

        .mon-field {
            width: 100%;
        }
    }
</style>