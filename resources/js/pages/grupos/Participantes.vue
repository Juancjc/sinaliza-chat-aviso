<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    Clipboard,
    Link,
    Link2Off,
    Trash2,
    UserRoundPlus,
    Users,
} from '@lucide/vue';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Select from 'primevue/select';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';

type Aluno = {
    id: number;
    name: string;
    email: string;
    avatar_emoji: string;
    pivot?: { status: 'convidado' | 'ativo' };
};

type Convite = {
    token: string;
    url: string;
    expires_at: string;
};

const props = defineProps<{
    grupo: { id: number; nome: string; descricao: string | null };
    participantes: Aluno[];
    alunosDisponiveis: Aluno[];
    conviteAtivo: Convite | null;
}>();

const participanteForm = useForm<{ user_id: number | null }>({ user_id: null });
const conviteForm = useForm({ duracao_horas: 24 });
const copiado = ref(false);
const conviteUrl = computed(() => {
    if (!props.conviteAtivo) {
        return '';
    }

    return typeof window === 'undefined'
        ? props.conviteAtivo.url
        : new URL(props.conviteAtivo.url, window.location.origin).toString();
});
const duracoes = [
    { label: '1 hora', value: 1 },
    { label: '24 horas', value: 24 },
    { label: '3 dias', value: 72 },
    { label: '7 dias', value: 168 },
];

const adicionar = () => {
    participanteForm.post(`/grupos/${props.grupo.id}/participantes`, {
        onSuccess: () => participanteForm.reset(),
    });
};

const gerarConvite = () => {
    conviteForm.post(`/grupos/${props.grupo.id}/convites`, {
        preserveScroll: true,
    });
};

const copiarConvite = async () => {
    if (!props.conviteAtivo) {
        return;
    }

    await navigator.clipboard.writeText(conviteUrl.value);
    copiado.value = true;
    window.setTimeout(() => {
        copiado.value = false;
    }, 2000);
};

const revogarConvite = () => {
    if (!props.conviteAtivo) {
        return;
    }

    router.delete(
        `/grupos/${props.grupo.id}/convites/${props.conviteAtivo.token}`,
        { preserveScroll: true },
    );
};

const removerParticipante = (aluno: Aluno) => {
    if (!window.confirm(`Remover ${aluno.name} deste grupo?`)) {
        return;
    }

    router.delete(`/grupos/${props.grupo.id}/participantes/${aluno.id}`, {
        preserveScroll: true,
    });
};

const formatarExpiracao = (date: string) =>
    new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(date));
</script>

<template>
    <Head :title="`Participantes - ${grupo.nome}`" />

    <main class="mx-auto w-full max-w-6xl p-4 md:p-8">
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
            <div class="space-y-6">
                <Card class="border border-border shadow-sm">
                    <template #title>Link temporário</template>
                    <template #subtitle>
                        Compartilhe o link para alunos entrarem no grupo.
                    </template>
                    <template #content>
                        <div class="mt-4 space-y-4">
                            <Message v-if="conviteAtivo" severity="success">
                                Link válido até
                                {{
                                    formatarExpiracao(conviteAtivo.expires_at)
                                }}.
                            </Message>

                            <div v-if="conviteAtivo" class="flex gap-2">
                                <InputText
                                    :model-value="conviteUrl"
                                    readonly
                                    class="min-w-0 flex-1"
                                />
                                <Button
                                    v-tooltip.top="'Copiar link'"
                                    severity="secondary"
                                    @click="copiarConvite"
                                >
                                    <template #icon>
                                        <Check v-if="copiado" class="size-4" />
                                        <Clipboard v-else class="size-4" />
                                    </template>
                                </Button>
                            </div>

                            <form
                                class="flex flex-col gap-3 sm:flex-row"
                                @submit.prevent="gerarConvite"
                            >
                                <Select
                                    v-model="conviteForm.duracao_horas"
                                    :options="duracoes"
                                    option-label="label"
                                    option-value="value"
                                    class="flex-1"
                                />
                                <Button
                                    type="submit"
                                    :label="
                                        conviteAtivo
                                            ? 'Gerar novo link'
                                            : 'Gerar link'
                                    "
                                    :loading="conviteForm.processing"
                                >
                                    <template #icon
                                        ><Link class="size-4"
                                    /></template>
                                </Button>
                            </form>

                            <Button
                                v-if="conviteAtivo"
                                label="Revogar link atual"
                                severity="danger"
                                outlined
                                class="w-full"
                                @click="revogarConvite"
                            >
                                <template #icon
                                    ><Link2Off class="size-4"
                                /></template>
                            </Button>
                        </div>
                    </template>
                </Card>

                <Card class="border border-border shadow-sm">
                    <template #title>Adicionar aluno</template>
                    <template #subtitle>
                        O aluno terá acesso imediato ao chat.
                    </template>
                    <template #content>
                        <form
                            class="mt-4 space-y-4"
                            @submit.prevent="adicionar"
                        >
                            <Select
                                v-model="participanteForm.user_id"
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
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ option.email }}
                                        </div>
                                    </div>
                                </template>
                            </Select>
                            <InputError
                                :message="participanteForm.errors.user_id"
                            />
                            <Button
                                type="submit"
                                label="Adicionar ao grupo"
                                class="w-full"
                                :disabled="!participanteForm.user_id"
                                :loading="participanteForm.processing"
                            >
                                <template #icon
                                    ><UserRoundPlus class="size-4"
                                /></template>
                            </Button>
                        </form>
                    </template>
                </Card>
            </div>

            <Card class="h-fit border border-border shadow-sm">
                <template #title>
                    <span class="flex items-center gap-2">
                        <Users class="size-5 text-blue-600" />
                        Participantes
                    </span>
                </template>
                <template #subtitle>
                    {{ grupo.nome }} · {{ participantes.length }} aluno(s)
                </template>
                <template #content>
                    <DataTable
                        :value="participantes"
                        striped-rows
                        responsive-layout="scroll"
                        class="mt-3"
                    >
                        <Column header="Aluno">
                            <template #body="{ data }">
                                <div class="flex items-center gap-3">
                                    <Avatar
                                        :label="data.avatar_emoji || '🙂'"
                                        shape="circle"
                                        class="bg-muted text-lg"
                                    />
                                    <span class="font-medium">{{
                                        data.name
                                    }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column field="email" header="E-mail" />
                        <Column header="Ações" class="w-20 text-right">
                            <template #body="{ data }">
                                <Button
                                    v-tooltip.top="'Remover do grupo'"
                                    severity="danger"
                                    text
                                    rounded
                                    :aria-label="`Remover ${data.name}`"
                                    @click="removerParticipante(data)"
                                >
                                    <template #icon
                                        ><Trash2 class="size-4"
                                    /></template>
                                </Button>
                            </template>
                        </Column>
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
