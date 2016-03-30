all: project/web/component/aurouze/aurouze-preview.html 

project/web/component/aurouze/aurouze-preview.html: project/web/component/aurouze/fontcustom.yml project/web/component/aurouze/svg/cafard.svg  project/web/component/aurouze/svg/chenille.svg  project/web/component/aurouze/svg/marker.svg  project/web/component/aurouze/svg/moustique.svg project/web/component/aurouze/svg/pigeon.svg project/web/component/aurouze/svg/puce.svg project/web/component/aurouze/svg/rongeur.svg
	cd project/web/component/aurouze ; fontcustom compile -c fontcustom.yml