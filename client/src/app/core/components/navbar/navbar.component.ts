import { CommonModule } from '@angular/common';
import { Component, ElementRef, OnInit, Renderer2 } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';

@Component({
  selector: 'app-navbar',
  imports: [RouterLink, CommonModule],
  templateUrl: './navbar.component.html',
  styleUrl: './navbar.component.css',
})
export class NavbarComponent implements OnInit {
  link: string = '';
  accueil: boolean = false;
  resultat: boolean = false;
  adminEcole: boolean = false;
  adminEleve: boolean = false;
  adminMatiere: boolean = false;
  adminNote: boolean = false;
  // cours: boolean = false;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private renderer: Renderer2,
    private el: ElementRef
  ) {}

  dropdown(menu: string, arrow: string) {
    // console.log(menu, ' ', arrow);

    this.el.nativeElement.querySelector(menu).classList.toggle('hidden');
    this.el.nativeElement.querySelector(arrow).classList.toggle('rotate-180');
  }

  openSidebar() {
    this.el.nativeElement.querySelector('.sidebar').classList.toggle('hidden');
  }

  ngOnInit(): void {
    this.router.events.subscribe(() => {
      const currentRoute = this.router.url;
      this.accueil = currentRoute === '/accueil';
      this.resultat = currentRoute === '/resultat';
      this.adminEcole = currentRoute === '/administration/ecole';
      this.adminEleve = currentRoute === '/administration/eleve';
      this.adminMatiere = currentRoute === '/administration/matiere';
      this.adminNote = currentRoute === '/administration/note';
      // this.cours = (currenRoute.startsWith('/accueil'));
      //console.log(`current route : ${currenRoute} \n home : ${this.home} and cours : ${this.cours}`);
    });
  }
}
