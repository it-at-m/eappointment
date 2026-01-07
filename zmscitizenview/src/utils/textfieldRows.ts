export const countLines = (text) => {
  const tempDiv = document.createElement("div");

  const viewportWidth = window.innerWidth * 0.8;
  const width = Math.min(558, viewportWidth);

  tempDiv.style.width = `${width}px`;
  tempDiv.style.visibility = "hidden";
  tempDiv.style.position = "absolute";
  tempDiv.style.whiteSpace = "pre-wrap";
  tempDiv.style.font = "inherit";
  document.body.appendChild(tempDiv);

  tempDiv.innerText = text;

  const lineHeight = parseInt(getComputedStyle(tempDiv).lineHeight);
  const lines = Math.ceil(tempDiv.scrollHeight / lineHeight);

  document.body.removeChild(tempDiv);
  return lines + 2 || 2;
};

export const updateInputLines = (text, inputLines) => {
  const lineCount = countLines(text);
  inputLines.value = lineCount; // Aktualisiere die Zeilenanzahl
};

export const handleInput = (customerData, inputLines, event) => {
  const fieldName = event.target.name; // Angenommen, das Textfeld hat ein "name"-Attribut
  customerData.value[fieldName] = event.target.value; // Aktualisiere den entsprechenden Wert
  updateInputLines(event.target.value, inputLines); // Aktualisiere die Zeilenanzahl
};
