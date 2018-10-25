import AvailabilityDayPage from './page/availabilityDay'
import DepartmentDaysOffView from './block/department/daysOff'
import DepartmentLinksView from './block/department/links'
import TicketPrinterConfigView from './block/ticketprinter/config'
import CallDisplayConfigView from './block/calldisplay/config'
import SourceRequestsView from './block/source/requests'
import SourceProvidersView from './block/source/providers'
import bindReact from './lib/bindReact.js'

bindReact('.availabilityDayRoot', AvailabilityDayPage)
bindReact('[data-department-daysoff]', DepartmentDaysOffView)
bindReact('[data-department-links]', DepartmentLinksView)
bindReact('[data-ticketprinter-config]', TicketPrinterConfigView)
bindReact('[data-calldisplay-config]', CallDisplayConfigView)
bindReact('[data-source-requests]', SourceRequestsView)
bindReact('[data-source-providers]', SourceProvidersView)

console.log("Loaded react components...");
