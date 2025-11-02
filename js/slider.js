const sliderBox = document.querySelector(".slider-box");
const leftBtn = document.querySelector(".btn-left");
const rightBtn = document.querySelector(".btn-right");
const carouselImages = document.querySelectorAll(".slider-img");
const carouselSpeed = 5000;


let index = 0;

const handleCarousel = () => {
	index++;
	changeImage();
};

let startCarousel = setInterval(handleCarousel, carouselSpeed);

const changeImage = () => {
	if (index > carouselImages.length - 1) {
		index = 0;
	} else if (index < 0) {
		index = carouselImages.length - 1;
	}

	if(index == 0){
		carouselImages[1].classList.remove('visible');
		carouselImages[2].classList.remove('visible');
		carouselImages[0].classList.add('visible');
	}else if(index == 1){
		carouselImages[0].classList.remove('visible');
		carouselImages[2].classList.remove('visible');
		carouselImages[1].classList.add('visible');
	}else if(index == 2){
		carouselImages[1].classList.remove('visible');
		carouselImages[0].classList.remove('visible');
		carouselImages[2].classList.add('visible');
	}
};

const handleRightArrow = () => {
	index++;
	resetInterval();
};

const handleLeftArrow = () => {
	index--;
	resetInterval();
};

const resetInterval = () => {
	changeImage();
	clearInterval(startCarousel);
	startCarousel = setInterval(handleCarousel, carouselSpeed);
};

rightBtn.addEventListener("click", handleRightArrow);
leftBtn.addEventListener("click", handleLeftArrow);
