import { computed, type Ref } from 'vue'

export function useDcf(params: {
  freeCashFlow: Ref<number>
  growthRates: Ref<number[]>
  discountRate: Ref<number>
  terminalGrowthRate: Ref<number>
  projectionYears: Ref<number>
  totalShares: Ref<number>
  currentPrice: Ref<number>
  netDebt: Ref<number>
}) {
  const baseYearFcf = computed(() => params.freeCashFlow.value)

  const projectedFcfs = computed(() => {
    const years = params.projectionYears.value
    const rates = params.growthRates.value
    let fcf = params.freeCashFlow.value
    const results: { year: number; fcf: number; growth: number; pv: number }[] = []
    const ke = params.discountRate.value / 100

    const currentYear = new Date().getFullYear()
    for (let i = 0; i < years; i++) {
      const g = (rates[i] ?? 0) / 100
      fcf *= (1 + g)
      const pv = fcf / Math.pow(1 + ke, i + 1)
      results.push({ year: currentYear + 1 + i, fcf, growth: rates[i] ?? 0, pv })
    }

    return results
  })

  const terminalValue = computed(() => {
    const lastFcf = projectedFcfs.value.length > 0
      ? projectedFcfs.value[projectedFcfs.value.length - 1].fcf
      : 0
    const ke = params.discountRate.value / 100
    const g = params.terminalGrowthRate.value / 100
    if (ke <= g) return 0
    return (lastFcf * (1 + g)) / (ke - g)
  })

  const pvTerminal = computed(() => {
    const ke = params.discountRate.value / 100
    const years = params.projectionYears.value
    return terminalValue.value / Math.pow(1 + ke, years)
  })

  const enterpriseValue = computed(() => {
    return projectedFcfs.value.reduce((sum, y) => sum + y.pv, 0) + pvTerminal.value
  })

  const equityValue = computed(() => {
    return enterpriseValue.value - params.netDebt.value
  })

  const fairPrice = computed(() => {
    if (params.totalShares.value <= 0) return 0
    return equityValue.value / params.totalShares.value
  })

  const upside = computed(() => {
    const cp = params.currentPrice.value
    const fp = fairPrice.value
    if (cp <= 0 || fp <= 0) return 0
    return ((fp - cp) / cp) * 100
  })

  const marginOfSafety = computed(() => {
    const cp = params.currentPrice.value
    const fp = fairPrice.value
    if (cp <= 0 || fp <= 0) return 0
    return ((fp - cp) / cp) * 100
  })

  const podeCalcular = computed(() =>
    params.freeCashFlow.value > 0 &&
    params.totalShares.value > 0 &&
    params.discountRate.value > params.terminalGrowthRate.value,
  )

  return {
    baseYearFcf,
    projectedFcfs,
    terminalValue,
    pvTerminal,
    enterpriseValue,
    equityValue,
    fairPrice,
    upside,
    marginOfSafety,
    podeCalcular,
  }
}
