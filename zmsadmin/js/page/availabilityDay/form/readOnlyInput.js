import { forwardRef } from 'react'


export const ReadOnlyInput = forwardRef(({ value, onClick, className }, ref) => (
    <input
        value={value}
        onClick={onClick}
        ref={ref}
        readOnly
        className={className}
        style={{ cursor: 'pointer' }}
    />
));
