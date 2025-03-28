<script setup>
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
</script>

<template>
  <!-- 👉 Dashboards -->
  <VerticalNavGroup
    :item="{
      title: 'Dashboard',
      icon: 'bx-bar-chart',
    }"
  >
    <VerticalNavLink
      :item="{
        title: 'Analytics',
        to: '/dashboard',
      }"
    />
  </VerticalNavGroup>

  <!-- Investimentos -->
  <VerticalNavGroup
    :item="{
      title: 'Investimentos',
      icon: 'bx-dollar-circle',
    }"
  >
    <VerticalNavLink
      :item="{
        title: 'Listar',
        to: '/investimentos',
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Novo',
        to: '/investimentos',
      }"
    />
  </VerticalNavGroup>

  <!-- Receitas -->
  <VerticalNavGroup
    :item="{
      title: 'Receitas',
      icon: 'bx-trending-up',
    }"
  >
    <VerticalNavLink
      :item="{
        title: 'Listar',
        to: '/receitas',
      }"
    />

    <VerticalNavLink
      :item="{
        title: 'Cadastrar',
        to: '/receitas',
      }"
    />

    <VerticalNavLink
      :item="{
        title: 'Categorias',
        to: '/receitas',
      }"
    />
  </VerticalNavGroup>

  <!-- Despesas -->
  <VerticalNavGroup
    :item="{
      title: 'Despesas',
      icon: 'bx-trending-down',
    }"
  >
    <VerticalNavLink
      :item="{
        title: 'Listar',
        to: '/receitas',
      }"
    />

    <VerticalNavLink
      :item="{
        title: 'Cadastrar',
        to: '/receitas',
      }"
    />

    <VerticalNavLink
      :item="{
        title: 'Categorias',
        to: '/receitas',
      }"
    />
  </VerticalNavGroup>

  <!-- Contas -->
  <VerticalNavGroup
    :item="{
      title: 'Contas',
      icon: 'bx-wallet',
    }"
  >
    <VerticalNavLink
      :item="{
        title: 'Listar',
        to: '/receitas',
      }"
    />

    <VerticalNavLink
      :item="{
        title: 'Cadastrar',
        to: '/receitas',
      }"
    />
  </VerticalNavGroup>

</template>
