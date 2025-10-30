import * as XLSX from 'xlsx';

export const exportExcel = ({ data, fileName, sheetName = 'Sheet1' }) => {
    try {
        // Create workbook
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        
        // Add worksheet to workbook
        XLSX.utils.book_append_sheet(wb, ws, sheetName);
        
        // Generate file and download
        XLSX.writeFile(wb, fileName);
    } catch (error) {
        console.error('Error exporting Excel file:', error);
        throw error;
    }
};