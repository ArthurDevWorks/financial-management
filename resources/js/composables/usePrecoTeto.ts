import { computed, type Ref } from 'vue';

export function usePrecoTeto(params: {
    desiredYield: Ref<number>;
    projectedPayout: Ref<number>;
    projectedNetIncome: Ref<number>;
    totalShares: Ref<number>;
    projectedGrowthRate: Ref<number>;
    currentPrice: Ref<number>;
}) {
    const lucroProjetado = computed(() => {
        const lucro = params.projectedNetIncome.value;
        if (lucro <= 0) return 0;
        return lucro * (1 + params.projectedGrowthRate.value / 100);
    });

    const lpaProjetado = computed(() => {
        const acoes = params.totalShares.value;
        if (acoes <= 0 || lucroProjetado.value <= 0) return 0;
        return lucroProjetado.value / acoes;
    });

    const dpaProjetado = computed(() => {
        const payout = params.projectedPayout.value / 100;
        if (payout <= 0 || lpaProjetado.value <= 0) return 0;
        return lpaProjetado.value * payout;
    });

    const precoTeto = computed(() => {
        const yieldDesejado = params.desiredYield.value / 100;
        if (yieldDesejado <= 0 || dpaProjetado.value <= 0) return 0;
        return dpaProjetado.value / yieldDesejado;
    });

    const yieldProjetado = computed(() => {
        const preco = params.currentPrice.value;
        if (preco <= 0 || dpaProjetado.value <= 0) return 0;
        return (dpaProjetado.value / preco) * 100;
    });

    const margemSeguranca = computed(() => {
        const preco = params.currentPrice.value;
        if (preco <= 0 || precoTeto.value <= 0) return 0;
        return ((precoTeto.value - preco) / preco) * 100;
    });

    const podeCalcular = computed(
        () =>
            params.desiredYield.value > 0 &&
            params.projectedPayout.value > 0 &&
            params.projectedNetIncome.value > 0 &&
            lucroProjetado.value > 0 &&
            params.totalShares.value > 0,
    );

    return {
        lucroProjetado,
        lpaProjetado,
        dpaProjetado,
        precoTeto,
        yieldProjetado,
        margemSeguranca,
        podeCalcular,
    };
}
