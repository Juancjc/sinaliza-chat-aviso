<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, UserRoundPlus, Users } from '@lucide/vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Select from 'primevue/select';
import InputError from '@/components/InputError.vue';

type Aluno = {
    id: number;
    name: string;
    email: string;
    pivot?: { status: 'convidado' | 'ativo' };
};

const props = defineProps<{
    grupo: { id: number; nome: string; descricao: string | null };
    participantes: Aluno[];
    alunosDisponiveis: Aluno[];
}>();

const form = useForm<{ user_id: number | null }>({ user_id: null });

const adicionar = () => {
    form.post(`/grupos/${props.grupo.id}/participantes`, {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head :title="`Participantes - ${grupo.nome}`" />

    <main class="mx-auto w-full max-w-5xl p-4 md:p-8">
        <Button
            label="Voltar"
            severity="secondary"
            text
            class="mb-4"
            @click="router.visit('/dashboard')"
        >
            <template #icon><ArrowLeft class="size-4" /></template>
        </Button>

        <div class="grid gap-6 lg:grid-cols-[1fr_2fr]">
            <Card class="h-fit border border-border shadow-sm">
                <template #title>Adicionar aluno</template>
                <template #subtitle
                    >O aluno terá acesso imediato ao chat.</template
                >
                <template #content>
                    <form class="mt-4 space-y-4" @submit.prevent="adicionar">
                        <Select
                            v-model="form.user_id"
                            :options="alunosDisponiveis"
                            option-label="name"
                            option-value="id"
                            filter
                            class="w-full"
                            placeholder="Selecione um aluno"
                            :empty-message="'Nenhum aluno disponível'"
                        >
                            <template #option="{ option }">
                                <div>
                                    <div class="font-medium">
                                        {{ option.name }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ option.email }}
                                    </div>
                                </div>
                            </template>
                        </Select>
                        <InputError :message="form.errors.user_id" />
                        <Button
                            type="submit"
                            label="Adicionar ao grupo"
                            class="w-full"
                            :disabled="!form.user_id"
                            :loading="form.processing"
                        >
                            <template #icon
                                ><UserRoundPlus class="size-4"
                            /></template>
                        </Button>
                    </form>
                </template>
            </Card>

            <Card class="border border-border shadow-sm">
                <template #title>
                    <span class="flex items-center gap-2">
                        <Users class="size-5 text-blue-600" />
                        Participantes
                    </span>
                </template>
                <template #subtitle
                    >{{ grupo.nome }} ·
                    {{ participantes.length }} aluno(s)</template
                >
                <template #content>
                    <DataTable
                        :value="participantes"
                        striped-rows
                        responsive-layout="scroll"
                        class="mt-3"
                    >
                        <Column field="name" header="Nome" />
                        <Column field="email" header="E-mail" />
                        <template #empty>
                            <div class="py-8 text-center text-muted-foreground">
                                Nenhum aluno foi adicionado ainda.
                            </div>
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </main>
</template>
