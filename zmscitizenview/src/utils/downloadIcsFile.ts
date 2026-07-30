export function downloadIcsFile(icsContent?: string): void {
  if (!icsContent) {
    return;
  }

  const blob = new Blob([icsContent], {
    type: "text/calendar;charset=utf-8",
  });

  const url = window.URL.createObjectURL(blob);
  const link = document.createElement("a");

  link.href = url;
  link.download = "Termin.ics";

  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  window.URL.revokeObjectURL(url);
}
