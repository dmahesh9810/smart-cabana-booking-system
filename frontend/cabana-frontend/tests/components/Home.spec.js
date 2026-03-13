import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Home from '../../src/pages/Home.vue';
import { createTestingPinia } from '@pinia/testing';
import { useCabanaStore } from '../../src/store/cabanaStore';

describe('Home.vue', () => {
    it('displays loading state', () => {
        const wrapper = mount(Home, {
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        cabana: { loading: true, error: null, cabanas: [] }
                    }
                })],
                stubs: { CabanaCard: true, 'router-link': true }
            }
        });

        expect(wrapper.find('.animate-spin').exists()).toBe(true);
    });

    it('displays error message when API fails', () => {
        const wrapper = mount(Home, {
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        cabana: { loading: false, error: 'Failed to load cabanas', cabanas: [] }
                    }
                })],
                stubs: { CabanaCard: true, 'router-link': true }
            }
        });

        expect(wrapper.text()).toContain('Failed to load cabanas');
    });

    it('renders a list of cabanas', async () => {
        const wrapper = mount(Home, {
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        cabana: {
                            loading: false,
                            error: null,
                            cabanas: [
                                { id: 1, name: 'Ocean Cabana', price_per_night: 150 },
                                { id: 2, name: 'Mountain Cabana', price_per_night: 100 }
                            ]
                        }
                    }
                })],
                stubs: { CabanaCard: true, 'router-link': true }
            }
        });

        // Check if cabanacard is rendered twice
        const cards = wrapper.findAllByTestId ? wrapper.findAllComponents({ name: 'CabanaCard' }) : wrapper.findAll('cabana-card-stub');
        expect(cards.length).toBe(2);
    });
});
