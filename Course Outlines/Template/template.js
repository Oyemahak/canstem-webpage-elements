// Toggle unit section content
document.querySelectorAll('.unit-toggle').forEach(button => {
    button.addEventListener('click', () => {
      const content = button.nextElementSibling;
      const isVisible = content.style.display === 'block';
      
      // Hide all open sections first
      document.querySelectorAll('.unit-content').forEach(sec => sec.style.display = 'none');
      
      // Toggle the clicked section
      content.style.display = isVisible ? 'none' : 'block';
    });
  });