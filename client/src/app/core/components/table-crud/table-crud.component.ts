import { CommonModule } from '@angular/common';
import { Component, Input, TemplateRef } from '@angular/core';
import { SharedDataService } from '../../services/shared-data.service';

@Component({
  selector: 'app-table-crud',
  imports: [CommonModule],
  templateUrl: './table-crud.component.html',
  styleUrl: './table-crud.component.css'
})
export class TableCrudComponent {
 @Input() columns: string[] = [];
 @Input() data: any[] = [];
 @Input() actionTemplate?: TemplateRef<any>;


 constructor(private sharedItemService: SharedDataService) {}

 selectItem(item: any) : void {
  this.sharedItemService.setSelectedItem(item);
 }
 
}
