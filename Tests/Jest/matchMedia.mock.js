Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: () => {
        return {
            matches: false,
            media: query,
            onchange: null,
            addEventListener: jest.fn(),
            removeEventListener: jest.fn(),
            dispatchEvent: jest.fn(),
            bind: jest.fn()
        };
    }
});
