import { JSDOM } from 'jsdom';
import DOMPurify from 'dompurify';

// Create a JSDOM instance for DOMPurify
const dom = new JSDOM('<!DOCTYPE html><html><body></body></html>');
global.window = dom.window as any;
global.document = dom.window.document;
global.DOMPurify = DOMPurify(dom.window);
