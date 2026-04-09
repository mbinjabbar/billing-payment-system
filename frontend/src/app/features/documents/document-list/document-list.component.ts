import { Component, inject, signal } from '@angular/core';
import { DocumentService } from '../../../core/services/document.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-document-list',
  imports: [CommonModule],
  templateUrl: './document-list.component.html',
  styleUrl: './document-list.component.css'
})
export class DocumentListComponent {
  private documentService = inject(DocumentService);
  documents = signal<any>([])

  ngOnInit(){
    this.loadDocuments()
    console.log("works")
  }

  loadDocuments(){
    this.documentService.getDocuments().subscribe((res:any) => {
      console.log(res)
      this.documents.set(res.data);
    }) 
  }

  
}
