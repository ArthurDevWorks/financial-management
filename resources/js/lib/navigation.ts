import type { LucideProps } from 'lucide-vue-next';
import {
    ArrowRightLeft,
    ChartNoAxesCombined,
    CreditCard,
    Landmark,
    LayoutDashboard,
    LogOut,
    Search,
    Settings,
    Tags,
    Wallet,
} from 'lucide-vue-next';
import type { FunctionalComponent } from 'vue';

export interface NavItem {
    label: string;
    icon: FunctionalComponent<LucideProps>;
    href: string;
    method?: 'get' | 'post';
    danger?: boolean;
}

export interface NavSection {
    title: string;
    items: NavItem[];
}

export const navSections: NavSection[] = [
    {
        title: 'Financeiro',
        items: [
            { label: 'Dashboard', icon: LayoutDashboard, href: '/dashboard' },
            { label: 'Bancos', icon: Landmark, href: '/banks' },
            { label: 'Contas', icon: Wallet, href: '/accounts' },
            { label: 'Lançamentos', icon: ArrowRightLeft, href: '/releases' },
            { label: 'Categorias', icon: Tags, href: '/categories' },
            { label: 'Bancos', icon: Landmark, href: '/banks' },
            { label: 'Cartões', icon: CreditCard, href: '/credit-cards' },
            { label: 'Categorias', icon: Tags, href: '/categories' },
            { label: 'Contas', icon: Wallet, href: '/accounts' },
            { label: 'Dashboard', icon: LayoutDashboard, href: '/dashboard' },
            { label: 'Lançamentos', icon: ArrowRightLeft, href: '/releases' },
        ],
    },
    {
        title: 'Investimentos',
        items: [
            { label: 'Screening', icon: Search, href: '/screening' },
            { label: 'Valuations', icon: ChartNoAxesCombined, href: '/valuations' },
        ],
    },
];

export const footerNavItems: NavItem[] = [
    { label: 'Configurações', icon: Settings, href: '/settings' },
    { label: 'Sair', icon: LogOut, href: '/logout', method: 'post', danger: true },
];
