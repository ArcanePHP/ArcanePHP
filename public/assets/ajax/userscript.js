document.addEventListener("DOMContentLoaded", function () {
  let elements = document.querySelectorAll(".favourite");

  elements.forEach((element) => {
    element.addEventListener("click", function (e) {
      let endParent = getEndParent(e.target);
      let valuetaker = endParent.querySelector(
        'input[type="hidden"][ajax-value-taker]'
      );

      let product_id = valuetaker.getAttribute("product_id");

      let data = {
        product_id: product_id,
      };

      ajaxCall(
        data,
        "post",
        "",
        "http://localhost/shop/addtofav/" + data.product_id
      );
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  let elements = document.querySelectorAll(".add-to-cart");

  elements.forEach((element) => {
    element.addEventListener("click", function (e) {
      let res =  getEventHandlers(element);
      console.log('heeey');
      
      console.log(res);
      
      let endParent = getEndParent(e.target);
      let valuetaker = endParent.querySelector(
        'input[type="hidden"][ajax-value-taker]'
      );

      let product_id = valuetaker.getAttribute("product_id");

      let data = {
        product_id: product_id,
      };

      ajaxCall(
        data,
        "post",
        "",
        "http://localhost/shop/addToCart/" + data.product_id
      );
    });
  });
});

// Function to get all event handlers attached to an element
function getEventHandlers(element) {
  const handlers = [];

  // Iterate through the event listeners on the element
  for (const type in element) {
    if (type.startsWith("on")) {
      const eventType = type.slice(2);
      const eventHandler = element[type];

      // Check if the event handler is a function
      if (typeof eventHandler === "function") {
        handlers.push({ type: eventType, handler: eventHandler });
      }
    }
  }

  return handlers;
}

// Get the card element
// const card = document.querySelector(".product-card");

// Get the event handlers attached to the card element
// const cardEventHandlers = getEventHandlers(card);

// Log the event handlers
// console.log("Event handlers attached to the card element:", cardEventHandlers);
