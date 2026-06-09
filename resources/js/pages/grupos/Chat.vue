<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Send, Users } from '@lucide/vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Textarea from 'primevue/textarea';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import InputError from '@/components/InputError.vue';
import type { RealtimeMessage } from '@/types/realtime';

type Mensagem = {
    id: number;
    mensagem: string;
    created_at: string;
    user: {
        id: number;
        name: string;
        tipo_usuario: 'admin' | 'aluno';
        avatar_emoji: string;
    };
};

const props = defineProps<{
    grupo: {
        id: number;
        nome: string;
        descricao: string | null;
        participantes_count: number;
        admin: { id: number; name: string };
    };
    mensagens: Mensagem[];
}>();

const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const chatMessages = ref<Mensagem[]>([...props.mensagens]);
const messagesBox = ref<HTMLElement | null>(null);
const form = useForm({ mensagem: '' });

const scrollToBottom = async () => {
    await nextTick();

    if (messagesBox.value) {
        messagesBox.value.scrollTop = messagesBox.value.scrollHeight;
    }
};

const enviar = () => {
    form.post(`/grupos/${props.grupo.id}/mensagens`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const receiveMessage = (event: Event) => {
    const message = (event as CustomEvent<RealtimeMessage>).detail;

    if (
        message.grupo.id !== props.grupo.id ||
        chatMessages.value.some(({ id }) => id === message.id)
    ) {
        return;
    }

    chatMessages.value.push(message);
    scrollToBottom();
};

const formatarHorario = (date: string) =>
    new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date));

watch(
    () => props.mensagens,
    (messages) => {
        chatMessages.value = [...messages];
        scrollToBottom();
    },
);

onMounted(() => {
    scrollToBottom();
    window.addEventListener('chat:message', receiveMessage);
});

onBeforeUnmount(() => {
    window.removeEventListener('chat:message', receiveMessage);
});
</script>

<template>
    <Head :title="`Chat - ${grupo.nome}`" />

    <main
        class="mx-auto flex h-[calc(100vh-4rem)] w-full max-w-6xl flex-col p-3 md:p-6"
    >
        <Card
            class="flex min-h-0 flex-1 flex-col overflow-hidden border border-border shadow-sm"
        >
            <template #title>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <Button
                            severity="secondary"
                            rounded
                            text
                            @click="router.visit('/dashboard')"
                        >
                            <template #icon
                                ><ArrowLeft class="size-5"
                            /></template>
                        </Button>
                        <div class="min-w-0">
                            <h1 class="truncate text-xl font-semibold">
                                {{ grupo.nome }}
                            </h1>
                            <span
                                class="flex items-center gap-1.5 text-sm font-normal text-muted-foreground"
                            >
                                <Users class="size-4" />
                                {{ grupo.participantes_count }} aluno(s) e
                                {{ grupo.admin.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>

            <template #content>
                <div
                    class="flex h-[calc(100vh-17rem)] min-h-[24rem] flex-col gap-4"
                >
                    <div
                        ref="messagesBox"
                        class="flex flex-1 flex-col gap-3 overflow-y-auto rounded-2xl bg-muted/40 p-4"
                    >
                        <div
                            v-if="!chatMessages.length"
                            class="m-auto max-w-sm text-center text-sm text-muted-foreground"
                        >
                            Ainda não há mensagens. Comece a conversa!
                        </div>

                        <div
                            v-for="mensagem in chatMessages"
                            :key="mensagem.id"
                            class="flex"
                            :class="
                                mensagem.user.id === currentUser.id
                                    ? 'justify-end'
                                    : 'justify-start'
                            "
                        >
                            <div
                                class="max-w-[85%] rounded-2xl px-4 py-3 shadow-sm md:max-w-[70%]"
                                :class="
                                    mensagem.user.id === currentUser.id
                                        ? 'rounded-br-sm bg-blue-600 text-white'
                                        : 'rounded-bl-sm border border-border bg-background'
                                "
                            >
                                <div
                                    class="mb-1 flex items-center gap-2 text-xs"
                                >
                                    <span class="font-semibold">{{
                                        `${mensagem.user.avatar_emoji || '🙂'} ${mensagem.user.name}`
                                    }}</span>
                                    <span
                                        :class="
                                            mensagem.user.id === currentUser.id
                                                ? 'text-blue-100'
                                                : 'text-muted-foreground'
                                        "
                                    >
                                        {{
                                            formatarHorario(mensagem.created_at)
                                        }}
                                    </span>
                                </div>
                                <p
                                    class="text-sm break-words whitespace-pre-wrap"
                                >
                                    {{ mensagem.mensagem }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <form class="flex items-end gap-2" @submit.prevent="enviar">
                        <div class="flex-1">
                            <Textarea
                                v-model="form.mensagem"
                                class="w-full"
                                rows="2"
                                maxlength="5000"
                                auto-resize
                                placeholder="Digite sua mensagem..."
                                @keydown.ctrl.enter="enviar"
                            />
                            <InputError :message="form.errors.mensagem" />
                        </div>
                        <Button
                            type="submit"
                            rounded
                            :loading="form.processing"
                            :disabled="!form.mensagem.trim()"
                        >
                            <template #icon><Send class="size-5" /></template>
                        </Button>
                    </form>
                </div>
            </template>
        </Card>
    </main>
</template>
