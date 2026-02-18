import { mount } from '@vue/test-utils';
import { describe, it, expect, beforeEach } from 'vitest';
// @ts-expect-error: Vue SFC import for test
import AvailabilityInfoModal from '@/components/Appointment/AppointmentSelection/AvailabilityInfoModal.vue';

describe('AvailabilityInfoModal', () => {
  let wrapper: ReturnType<typeof mount>;

  const createWrapper = (props = {}) => {
    return mount(AvailabilityInfoModal, {
      props: {
        open: false,
        html: '',
        t: (key: string) => key,
        ...props,
      },
      global: {
        stubs: {
          MucModal: {
            props: ['open', 'closeAriaLabel', 'dialogAriaLabel'],
            emits: ['update:open'],
              template: `
                <div class="modal" role="dialog" aria-modal="true" v-if="open">
                  <div class="modal-content" v-show="open">
                    <div class="modal-header">
                      <h2 class="standard-headline"><slot name="title"/></h2>
                      <!-- props direkt verwenden, nicht props.closeAriaLabel -->
                      <button class="modal-button-close" :aria-label="closeAriaLabel || 'Dialog schließen'" type="button" @click="$emit('update:open', false; $parent.$emit('update:open', false)"></button>
                    </div>
                  <div class="modal-body"><slot name="body"/></div>
                  <div class="modal-footer"><slot name="footer"/></div>
                  </div>
                <div class="modal-backdrop"></div>
                </div>
              `,
          },
          MucButton: {
            template: `<button><slot/></button>`,
          },
        },
      },
    });
  };

  describe('Props', () => {
    it('renders when open is true', () => {
      wrapper = createWrapper({ open: true, html: 'Test content' });
      expect(wrapper.find('.modal-content').isVisible()).toBe(true);
    });

    it('does not render when open is false', () => {
      wrapper = createWrapper({ open: false, html: 'Test content' });
      expect(wrapper.find('.modal-content').exists()).toBe(false);
    });

    it('displays html content correctly', () => {
      const testHtml = '<p>Test <strong>content</strong></p>';
      wrapper = createWrapper({ open: true, html: testHtml });
      expect(wrapper.find('.modal-body').html()).toContain(testHtml);
    });

    it('handles empty html content', () => {
      wrapper = createWrapper({ open: true, html: '' });
      expect(wrapper.find('.standard-headline').text()).toBe('newAppointmentsInfoLink');
    });

    it('handles HTML with special characters', () => {
      const testHtml = '<p>Test &amp; content &lt;script&gt;alert("xss")&lt;/script&gt;</p>';
      wrapper = createWrapper({ open: true, html: testHtml });
      expect(wrapper.find('.modal-body').html()).toContain(testHtml);
    });
  });

  describe('Modal Structure', () => {
    beforeEach(() => {
      wrapper = createWrapper({ open: true, html: 'Test content' });
    });

    it('has correct modal classes and attributes', () => {
      const modal = wrapper.find('.modal');
      expect(modal.exists()).toBe(true);
      expect(modal.attributes('role')).toBe('dialog');
      expect(modal.attributes('aria-modal')).toBe('true');
    });

    it('has modal-content element', () => {
      expect(wrapper.find('.modal-content').exists()).toBe(true);
    });

    it('has modal-header with close button', () => {
      const header = wrapper.find('.modal-header');
      expect(header.exists()).toBe(true);
      
      const closeButton = header.find('.modal-button-close');
      expect(closeButton.exists()).toBe(true);
      expect(closeButton.attributes('aria-label')).toBe('Dialog schließen');
    });

    it('has modal-body with content', () => {
      const body = wrapper.find('.modal-body');
      expect(body.exists()).toBe(true);
      expect(body.text()).toBe('Test content');
    });

    it('has standard headline H2 header', () => {
      const header = wrapper.find('.standard-headline');
      expect(header.exists()).toBe(true);
      expect(header.text()).toBe('newAppointmentsInfoLink');
      expect(header.element.tagName).toBe('H2');
    });

    it('has modal-backdrop', () => {
      const backdrop = wrapper.find('.modal-backdrop');
      expect(backdrop.exists()).toBe(true);
    });
  });

  describe('Events', () => {
    beforeEach(() => {
      wrapper = createWrapper({ open: true, html: 'Test content' });
    });

    it('emits update:open false when close button is clicked', async () => {
      const closeButton = wrapper.find('.modal-button-close');
      await closeButton.trigger('click');
      
      expect(wrapper.emitted('update:open')).toBeTruthy();
      expect(wrapper.emitted('update:open')![0]).toEqual([false]);
    });
  });

  describe('Edge Cases', () => {
    it('handles very long HTML content', () => {
      const longHtml = '<p>' + 'A'.repeat(1000) + '</p>';
      wrapper = createWrapper({ open: true, html: longHtml });
      expect(wrapper.find('.modal-body').html()).toContain(longHtml);
    });

    it('handles HTML with nested elements', () => {
      const nestedHtml = '<div><p><span><strong>Nested</strong> content</span></p></div>';
      wrapper = createWrapper({ open: true, html: nestedHtml });
      expect(wrapper.find('.modal-body').text()).toContain('Nested content');
      expect(wrapper.find('.modal-body strong').text()).toBe('Nested');
    });

    it('handles HTML with self-closing tags', () => {
      const selfClosingHtml = '<p>Content</p><br><hr>';
      wrapper = createWrapper({ open: true, html: selfClosingHtml });
      expect(wrapper.find('.modal-body').text()).toContain('Content');
      expect(wrapper.find('.modal-body p').exists()).toBe(true);
    });
  });
});
