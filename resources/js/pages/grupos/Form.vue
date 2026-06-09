<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from '@lucide/vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    grupo?: { id: number; nome: string; descricao: string | null };
}>();

const editando = Boolean(props.grupo);
const form = useForm({
    nome: props.grupo?.nome ?? '',
    descricao: props.grupo?.descricao ?? '',
});

const salvar = () => {
    if (props.grupo) {
        form.put(`/grupos/${props.grupo.id}`);

        return;
    }

    form.post('/grupos');
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Grupo', href: '/grupos/create' },
        ],
    },
});
</script>

<template>
    <Head :title="editando ? 'Editar grupo' : 'Criar grupo'" />

    <main class="mx-auto w-full max-w-3xl p-4 md:p-8">
        <Button
            label="Voltar"
            severity="secondary"
            text
            class="mb-4"
            @click="router.visit('/dashboard')"
        >
            <template #icon><ArrowLeft class="size-4" /></template>
        </Button>

        <Card class="border border-border shadow-sm">
            <template #title>{{
                editando ? 'Editar grupo' : 'Novo grupo'
            }}</template>
            <template #subtitle>
                Informe os dados que identificam este espaço de conversa.
            </template>
            <template #content>
                <form class="mt-4 space-y-6" @submit.prevent="salvar">
                    <div class="space-y-2">
                        <label for="nome" class="text-sm font-medium"
                            >Nome</label
                        >
                        <InputText
                            id="nome"
                            v-model="form.nome"
                            class="w-full"
                            maxlength="255"
                            autofocus
                            placeholder="Ex.: Turma de Desenvolvimento Web"
                        />
                        <InputError :message="form.errors.nome" />
                    </div>

                    <div class="space-y-2">
                        <label for="descricao" class="text-sm font-medium"
                            >Descrição</label
                        >
                        <Textarea
                            id="descricao"
                            v-model="form.descricao"
                            class="w-full"
                            rows="6"
                            maxlength="3000"
                            placeholder="Conte aos alunos qual é o objetivo deste grupo."
                        />
                        <InputError :message="form.errors.descricao" />
                    </div>

                    <div class="flex justify-end">
                        <Button
                            type="submit"
                            :label="
                                editando ? 'Salvar alterações' : 'Criar grupo'
                            "
                            :loading="form.processing"
                        >
                            <template #icon><Save class="size-4" /></template>
                        </Button>
                    </div>
                </form>
            </template>
        </Card>
    </main>
</template>
