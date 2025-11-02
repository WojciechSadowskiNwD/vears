const AccordionHelp = document.querySelector(".pull-out-block-help");
const accordionHelpBtn = document.querySelector(".help-btn");
const AccordionOrder = document.querySelector(".pull-out-block-order");
const pullOutBtns = document.querySelectorAll(".pull-out-block-btn");
const accordionNavBtns = document.querySelectorAll(".top-nav-btns");
const footerToAccordionOrderTop = document.querySelector(
	".status-order-footer-btn"
);
// złapane małe obrazki produktu
const allSmallImages = document.querySelectorAll(".small-square");
const firstBigImage = document.querySelector(".first-img-big");
const secondBigImage = document.querySelector(".second-img-big");
const thirdBigImage = document.querySelector(".third-img-big");
// złapana klasa pod przyciskiem ulubione
const favIcon = document.querySelector(".fav-icon");
const favInfo = document.querySelector(".fav-info");


function closeAccordion() {
	AccordionHelp.classList.remove("open-accordion");
	AccordionOrder.classList.remove("open-accordion");
}

const whichAccordionOpen = e => {
	if (e.target.classList.contains("help-btn")) {
		if (AccordionHelp.classList.contains("open-accordion")) {
			closeAccordion();
		} else {
			AccordionOrder.classList.remove("open-accordion");
			AccordionHelp.classList.add("open-accordion");
		}
	} else if (e.target.classList.contains("status-order-btn")) {
		if (AccordionOrder.classList.contains("open-accordion")) {
			closeAccordion();
		} else {
			AccordionHelp.classList.remove("open-accordion");
			AccordionOrder.classList.add("open-accordion");
		}
	}
};

const OpenAccordionOrder = () => {
	AccordionHelp.classList.remove("open-accordion");
	AccordionOrder.classList.add("open-accordion");
};

const changeImageProduct = e => {
	if (e.target.classList.contains("first-img")) {
		secondBigImage.classList.add("display-none");
		thirdBigImage.classList.add("display-none");
		firstBigImage.classList.remove("display-none");
	} else if (e.target.classList.contains("second-img")) {
		firstBigImage.classList.add("display-none");
		thirdBigImage.classList.add("display-none");
		secondBigImage.classList.remove("display-none");
	} else if (e.target.classList.contains("third-img")) {
		firstBigImage.classList.add("display-none");
		secondBigImage.classList.add("display-none");
		thirdBigImage.classList.remove("display-none");
	}
};

const showInfoBox = () => {
	favInfo.classList.add("displayBlock");
};
const closeInfoBox = () => {
	favInfo.classList.remove("displayBlock");
};



accordionNavBtns.forEach(btn =>
	btn.addEventListener("click", whichAccordionOpen)
);
pullOutBtns.forEach(btn => btn.addEventListener("click", closeAccordion));
allSmallImages.forEach(img =>
	img.addEventListener("click", changeImageProduct)
);
footerToAccordionOrderTop.addEventListener("click", OpenAccordionOrder);
favIcon.addEventListener("mouseenter", showInfoBox);
favIcon.addEventListener("mouseleave", closeInfoBox);
