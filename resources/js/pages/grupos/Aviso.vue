<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Mail, Send } from '@lucide/vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Textarea from 'primevue/textarea';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    grupo: {
        id: number;
        nome: string;
        descricao: string | null;
        participantes_count: number;
    };
}>();

const form = useForm({
    titulo: '',
    mensagem: '',
});

const enviar = () => {
    form.post(`/grupos/${props.grupo.id}/avisos`);
};
</script>

<template>
    <Head :title="`Enviar aviso - ${grupo.nome}`" />

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
            <template #title>
                <span class="flex items-center gap-2">
                    <Mail class="size-5 text-blue-600" />
                    Enviar aviso
                </span>
            </template>
            <template #subtitle>{{ grupo.nome }}</template>
            <template #content>
                <Message severity="info" class="my-5">
                    Este aviso será salvo e enviado por e-mail para
                    {{ grupo.participantes_count }} aluno(s).
                </Message>

                <form class="space-y-6" @submit.prevent="enviar">
                    <div class="space-y-2">
                        <label for="titulo" class="text-sm font-medium"
                            >Título</label
                        >
                        <InputText
                            id="titulo"
                            v-model="form.titulo"
                            class="w-full"
                            maxlength="255"
                            autofocus
                            placeholder="Assunto do aviso"
                        />
                        <InputError :message="form.errors.titulo" />
                    </div>

                    <div class="space-y-2">
                        <label for="mensagem" class="text-sm font-medium"
                            >Mensagem</label
                        >
                        <Textarea
                            id="mensagem"
                            v-model="form.mensagem"
                            class="w-full"
                            rows="9"
                            maxlength="10000"
                            placeholder="Escreva o aviso que será enviado aos alunos."
                        />
                        <InputError :message="form.errors.mensagem" />
                    </div>

                    <div class="flex justify-end">
                        <Button
                            type="submit"
                            label="Salvar e enviar aviso"
                            :loading="form.processing"
                            :disabled="
                                !form.titulo.trim() || !form.mensagem.trim()
                            "
                        >
                            <template #icon><Send class="size-4" /></template>
                        </Button>
                    </div>
                </form>
            </template>
        </Card>
    </main>
</template>
