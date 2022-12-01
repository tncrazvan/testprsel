export function fileToBase64(file: File): Promise<string> {
  return new Promise((r) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function () {
      r(reader.result as string);
    };
    reader.onerror = function (error) {
      r("");
      console.log("Error: ", error);
    };
  });
}
