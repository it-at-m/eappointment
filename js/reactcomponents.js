import AvailabilityDayPage from './page/availabilityDay'
import DepartmentDaysOffView from './block/department/daysOff'
import DepartmentLinksView from './block/department/links'
import TicketPrinterConfigView from './block/ticketprinter/config'
import CallDisplayConfigView from './block/calldisplay/config'
import bindReact from './lib/bindReact.js'

bindReact('.availabilityDayRoot', AvailabilityDayPage)
bindReact('[data-department-daysoff]', DepartmentDaysOffView)
bindReact('[data-department-links]', DepartmentLinksView)
bindReact('[data-ticketprinter-config]', TicketPrinterConfigView)
bindReact('[data-calldisplay-config]', CallDisplayConfigView)

console.log("Loaded react components...");
