import { Component } from '@angular/core';
import { ApiService } from '../../core/services/api.service';

@Component({
  selector: 'app-accueil',
  imports: [],
  templateUrl: './accueil.component.html',
  styleUrl: './accueil.component.css'
})
export class AccueilComponent {// Service Angular
  loading = false;
  error = null;


  // constructor(private apiService: ApiService) {}
  // async download() {
  //   this.loading = true;
  //   this.error = null;
    
  //   try {
  //     const pdfBlob = await this.apiService.generatePdf('001');
  //     saveAs(pdfBlob, `bulletin_${new Date().toISOString().slice(0,10)}.pdf`);
  //   } catch (err) {
  //     console.error('Échec du téléchargement:', err);
  //     this.error = 'Erreur lors du téléchargement. Veuillez réessayer.';
  //   } finally {
  //     this.loading = false;
  //   }
  // }
}
