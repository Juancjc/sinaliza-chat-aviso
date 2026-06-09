<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { Bell, Megaphone, MessageCircle } from '@lucide/vue';
import Avatar from 'primevue/avatar';
import PrimeButton from 'primevue/button';
import Dialog from 'primevue/dialog';
import Popover from 'primevue/popover';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import { Button } from '@/components/ui/button';
import type { UnreadNotification } from '@/types/notifications';

const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const notifications = ref<UnreadNotification[]>([
    ...page.props.unreadNotifications,
]);
const currentNotice = ref<UnreadNotification | null>(null);
const popover = ref<{
    hide: () => void;
    toggle: (event: Event) => void;
} | null>(null);

const unreadCount = computed(() => notifications.value.length);
const isStudent = computed(() => currentUser.value.tipo_usuario === 'aluno');

const summarize = (message: string) =>
    message.length > 110 ? `${message.slice(0, 107)}...` : message;

const csrfToken = () =>
    document
        .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';

const patch = async (url: string) => {
    const response = await fetch(url, {
        method: 'PATCH',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
    });

    if (!response.ok) {
        throw new Error('Não foi possível atualizar a notificação.');
    }
};

const openNextNotice = async () => {
    if (!isStudent.value || currentNotice.value) {
        return;
    }

    const notice = notifications.value.find(({ kind }) => kind === 'aviso');

    if (!notice) {
        return;
    }

    currentNotice.value = notice;
    await nextTick();
};

const refreshNotifications = async () => {
    const response = await fetch('/notificacoes/nao-lidas', {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        return;
    }

    notifications.value = await response.json();
    await openNextNotice();
};

const markRead = async (notification: UnreadNotification) => {
    await patch(`/notificacoes/${notification.id}/ler`);
    notifications.value = notifications.value.filter(
        ({ id }) => id !== notification.id,
    );
};

const readNotice = async () => {
    if (!currentNotice.value) {
        return;
    }

    await markRead(currentNotice.value);
    currentNotice.value = null;
    await nextTick();
    await openNextNotice();
};

const openNotification = async (notification: UnreadNotification) => {
    popover.value?.hide();

    if (notification.kind === 'aviso') {
        currentNotice.value = notification;

        return;
    }

    await markRead(notification);
    router.visit(`/grupos/${notification.group.id}/chat`);
};

const togglePopover = (event: Event) => {
    popover.value?.toggle(event);
};

const readGroupMessages = async (event: Event) => {
    const groupId = (event as CustomEvent<number>).detail;

    await patch(`/grupos/${groupId}/mensagens/ler`);
    await refreshNotifications();
};

useEcho<{ aviso_id: number }>(
    `users.${currentUser.value.id}`,
    '.aviso.enviado',
    refreshNotifications,
);

watch(
    () => page.props.unreadNotifications,
    (items) => {
        notifications.value = [...items];
    },
);

onMounted(() => {
    window.addEventListener('notifications:refresh', refreshNotifications);
    window.addEventListener('notifications:read-group', readGroupMessages);
    openNextNotice();
});

onBeforeUnmount(() => {
    window.removeEventListener('notifications:refresh', refreshNotifications);
    window.removeEventListener('notifications:read-group', readGroupMessages);
});
</script>

<template>
    <div>
        <Button
            variant="ghost"
            size="icon"
            class="relative"
            aria-label="Abrir notificações não lidas"
            @click="togglePopover"
        >
            <Bell class="size-5" />
            <span
                v-if="unreadCount"
                class="absolute -top-1 -right-1 flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] leading-5 font-bold text-white"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </Button>

        <Popover :key="unreadCount" ref="popover">
            <div class="w-[min(24rem,calc(100vw-2rem))]">
                <div class="border-b border-border px-1 pb-3">
                    <h2 class="font-semibold">Não lidas</h2>
                    <p class="text-xs text-muted-foreground">
                        {{ unreadCount }} mensagem(ns) ou aviso(s)
                    </p>
                </div>

                <div
                    v-if="!notifications.length"
                    class="py-8 text-center text-sm text-muted-foreground"
                >
                    Tudo lido por aqui.
                </div>

                <div v-else class="max-h-96 space-y-1 overflow-y-auto pt-2">
                    <button
                        v-for="notification in notifications"
                        :key="notification.id"
                        type="button"
                        class="flex w-full gap-3 rounded-xl p-3 text-left transition-colors hover:bg-muted"
                        @click="openNotification(notification)"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10"
                        >
                            <Megaphone
                                v-if="notification.kind === 'aviso'"
                                class="size-5 text-amber-600"
                            />
                            <MessageCircle
                                v-else
                                class="size-5 text-blue-600"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <span class="truncate text-sm font-semibold">
                                    {{ notification.title }}
                                </span>
                                <span
                                    class="shrink-0 text-[10px] font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    {{
                                        notification.kind === 'aviso'
                                            ? 'Aviso'
                                            : 'Mensagem'
                                    }}
                                </span>
                            </div>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ notification.group.nome }}
                            </p>
                            <p class="mt-1 text-xs break-words">
                                {{ summarize(notification.message) }}
                            </p>
                        </div>
                    </button>
                </div>
            </div>
        </Popover>

        <Dialog
            v-if="currentNotice"
            :visible="true"
            modal
            :closable="false"
            :close-on-escape="false"
            :dismissable-mask="false"
            class="w-[min(34rem,calc(100vw-2rem))]"
        >
            <template #header>
                <div class="flex min-w-0 items-center gap-3 pr-4">
                    <Avatar
                        :label="currentNotice?.sender.avatar_emoji || '📢'"
                        shape="circle"
                        class="shrink-0 bg-amber-100 text-xl"
                    />
                    <div class="min-w-0">
                        <div class="truncate text-sm text-muted-foreground">
                            {{ currentNotice?.group.nome }}
                        </div>
                        <div class="truncate text-lg font-semibold">
                            {{ currentNotice?.title }}
                        </div>
                    </div>
                </div>
            </template>

            <div
                class="max-h-[55vh] overflow-y-auto rounded-xl bg-muted/40 p-4 text-sm leading-relaxed break-words whitespace-pre-wrap"
            >
                {{ currentNotice?.message }}
            </div>

            <template #footer>
                <PrimeButton
                    label="Li este aviso"
                    class="w-full sm:w-auto"
                    @click="readNotice"
                />
            </template>
        </Dialog>
    </div>
</template>
