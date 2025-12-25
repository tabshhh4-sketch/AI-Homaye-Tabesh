import { create } from 'zustand';

/**
 * Zustand store for Homa chat state management
 */
export const useHomaStore = create((set) => ({
    messages: [],
    userPersona: null,
    isTyping: false,
    sidebarOpen: false,

    addMessage: (message) => set((state) => ({
        messages: [...state.messages, message]
    })),

    clearMessages: () => set({ messages: [] }),

    setUserPersona: (persona) => set({ userPersona: persona }),

    setIsTyping: (isTyping) => set({ isTyping }),

    setSidebarOpen: (isOpen) => set({ sidebarOpen: isOpen }),

    updateLastMessage: (updates) => set((state) => {
        const messages = [...state.messages];
        if (messages.length > 0) {
            messages[messages.length - 1] = {
                ...messages[messages.length - 1],
                ...updates
            };
        }
        return { messages };
    })
}));
