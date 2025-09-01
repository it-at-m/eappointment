import { mount } from '@vue/test-utils';
import { describe, it, expect, beforeEach } from 'vitest';
// @ts-expect-error: Vue SFC import for test
import AvailabilityInfoModal from '@/components/Appointment/AvailabilityInfoModal.vue';

describe('AvailabilityInfoModal', () => {
  let wrapper: ReturnType<typeof mount>;

  const createWrapper = (props = {}) => {
    return mount(AvailabilityInfoModal, {
      props: {
        show: false,
        html: '',
        ...props,
      },
    });
  };

  describe('Props', () => {
    it('renders when show is true', () => {
      wrapper = createWrapper({ show: true, html: 'Test content' });
      expect(wrapper.find('.modal').exists()).toBe(true);
      expect(wrapper.find('.modal-backdrop').exists()).toBe(true);
    });

    it('does not render when show is false', () => {
      wrapper = createWrapper({ show: false, html: 'Test content' });
      expect(wrapper.find('.modal').exists()).toBe(false);
      expect(wrapper.find('.modal-backdrop').exists()).toBe(false);
    });

    it('displays html content correctly', () => {
      const testHtml = '<p>Test <strong>content</strong></p>';
      wrapper = createWrapper({ show: true, html: testHtml });
      expect(wrapper.find('.modal-body').html()).toContain(testHtml);
    });

    it('handles empty html content', () => {
      wrapper = createWrapper({ show: true, html: '' });
      expect(wrapper.find('.modal-body').text()).toBe('');
    });

    it('handles HTML with special characters', () => {
      const testHtml = '<p>Test &amp; content &lt;script&gt;alert("xss")&lt;/script&gt;</p>';
      wrapper = createWrapper({ show: true, html: testHtml });
      expect(wrapper.find('.modal-body').html()).toContain(testHtml);
    });
  });

  describe('Modal Structure', () => {
    beforeEach(() => {
      wrapper = createWrapper({ show: true, html: 'Test content' });
    });

    it('has correct modal classes and attributes', () => {
      const modal = wrapper.find('.modal');
      expect(modal.classes()).toEqual(expect.arrayContaining(['fade', 'show']));
      expect(modal.attributes('role')).toBe('dialog');
      expect(modal.attributes('aria-modal')).toBe('true');
    });

    it('has modal-dialog with centered class', () => {
      expect(wrapper.find('.modal-dialog').classes()).toContain('modal-dialog-centered');
    });

    it('has modal-header with close button', () => {
      const header = wrapper.find('.modal-header');
      expect(header.exists()).toBe(true);
      
      const closeButton = header.find('.modal-button-close');
      expect(closeButton.exists()).toBe(true);
      expect(closeButton.attributes('aria-label')).toBe('Dialog schliessen');
    });

    it('has modal-body with content', () => {
      const body = wrapper.find('.modal-body');
      expect(body.exists()).toBe(true);
      expect(body.text()).toBe('Test content');
    });

    it('does not have modal-footer', () => {
      expect(wrapper.find('.modal-footer').exists()).toBe(false);
    });

    it('has modal-backdrop', () => {
      expect(wrapper.find('.modal-backdrop').exists()).toBe(true);
      expect(wrapper.find('.modal-backdrop').classes()).toEqual(expect.arrayContaining(['fade', 'show']));
    });
  });

  describe('Events', () => {
    beforeEach(() => {
      wrapper = createWrapper({ show: true, html: 'Test content' });
    });

    it('emits close event when close button is clicked', async () => {
      const closeButton = wrapper.find('.modal-button-close');
      await closeButton.trigger('click');
      
      expect(wrapper.emitted('close')).toBeTruthy();
      expect(wrapper.emitted('close')).toHaveLength(1);
    });

    it('emits close event when clicking outside modal content', async () => {
      const modal = wrapper.find('.modal');
      await modal.trigger('click');
      
      expect(wrapper.emitted('close')).toBeTruthy();
      expect(wrapper.emitted('close')).toHaveLength(1);
    });

    it('emits close event when clicking on backdrop', async () => {
      const backdrop = wrapper.find('.modal-backdrop');
      await backdrop.trigger('click');
      
      expect(wrapper.emitted('close')).toBeTruthy();
      expect(wrapper.emitted('close')).toHaveLength(1);
    });

    it('does not emit close when clicking inside modal content', async () => {
      const modalBody = wrapper.find('.modal-body');
      await modalBody.trigger('click');
      
      expect(wrapper.emitted('close')).toBeFalsy();
    });
  });

  describe('Styling', () => {
    beforeEach(() => {
      wrapper = createWrapper({ show: true, html: 'Test content' });
    });

    it('renders modal-body element', () => {
      expect(wrapper.find('.modal-body').exists()).toBe(true);
    });

    it('has correct modal display style when shown', () => {
      const modal = wrapper.find('.modal');
      expect(modal.attributes('style')).toContain('display: block');
    });
  });

  describe('Accessibility', () => {
    beforeEach(() => {
      wrapper = createWrapper({ show: true, html: 'Test content' });
    });

    it('has proper ARIA attributes', () => {
      const modal = wrapper.find('.modal');
      expect(modal.attributes('role')).toBe('dialog');
      expect(modal.attributes('aria-modal')).toBe('true');
    });

    it('has close button with proper aria-label', () => {
      const closeButton = wrapper.find('.modal-button-close');
      expect(closeButton.attributes('aria-label')).toBe('Dialog schliessen');
    });

    it('close button has proper type attribute', () => {
      const closeButton = wrapper.find('.modal-button-close');
      expect(closeButton.attributes('type')).toBe('button');
    });
  });

  describe('Edge Cases', () => {
    it('handles very long HTML content', () => {
      const longHtml = '<p>' + 'A'.repeat(1000) + '</p>';
      wrapper = createWrapper({ show: true, html: longHtml });
      
      expect(wrapper.find('.modal-body').html()).toContain(longHtml);
    });

    it('handles HTML with nested elements', () => {
      const nestedHtml = '<div><p><span><strong>Nested</strong> content</span></p></div>';
      wrapper = createWrapper({ show: true, html: nestedHtml });
      
      // Vue wraps the content in additional divs, so we check for the content within
      expect(wrapper.find('.modal-body').text()).toContain('Nested content');
      expect(wrapper.find('.modal-body strong').text()).toBe('Nested');
    });

    it('handles HTML with self-closing tags', () => {
      const selfClosingHtml = '<p>Content</p><br><hr>';
      wrapper = createWrapper({ show: true, html: selfClosingHtml });
      
      // Vue wraps the content in additional divs, so we check for the content within
      expect(wrapper.find('.modal-body').text()).toContain('Content');
      expect(wrapper.find('.modal-body p').exists()).toBe(true);
      expect(wrapper.find('.modal-body br').exists()).toBe(true);
      expect(wrapper.find('.modal-body hr').exists()).toBe(true);
    });
  });
});
