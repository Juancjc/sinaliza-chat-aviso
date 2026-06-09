export type UnreadNotification = {
    id: string;
    kind: 'mensagem' | 'aviso';
    message_id?: number;
    aviso_id?: number;
    group: {
        id: number;
        nome: string;
    };
    title: string;
    message: string;
    sender: {
        id: number;
        name: string;
        avatar_emoji: string;
    };
    created_at: string;
};
