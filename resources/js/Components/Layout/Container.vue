<template>
    <div class="min-h-screen">
        <!-- Header + Action -->
        <div v-if="$slots.header || $slots.actions">
            <div>
                <slot name="header" />
            </div>

            <div>
                <slot name="actions" />
            </div>
        </div>

        <!-- Stats -->
        <div v-if="$slots.statCards">
            <slot name="statCards" />
        </div>

        <!-- Filters -->
        <div v-if="$slots.filters">
            <slot name="filters" />
        </div>

        <!-- Content -->
        <div v-if="$slots.content">
            <slot name="content" />
            <!-- Table -->
            <slot />
        </div>
    </div>
</template>
