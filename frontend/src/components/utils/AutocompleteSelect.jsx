import React, { useState, useRef, useEffect } from 'react';

function AutocompleteSelect({ options, value, onChange, placeholder }) {
  const [inputValue, setInputValue] = useState('');
  const [filteredOptions, setFilteredOptions] = useState([]);
  const [isListOpen, setIsListOpen] = useState(false);
  const wrapperRef = useRef(null);

  useEffect(() => {
    const selectedOption = options.find(option => option.id === value);
    setInputValue(selectedOption ? selectedOption.name : '');
  }, [value, options]);

  useEffect(() => {
    function handleClickOutside(event) {
      if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
        setIsListOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, [wrapperRef]);

  const handleInputChange = (e) => {
    const query = e.target.value;
    setInputValue(query);
    if (query) {
      setFilteredOptions(
        options.filter(option =>
          option.name.toLowerCase().includes(query.toLowerCase())
        )
      );
      setIsListOpen(true);
    } else {
      setFilteredOptions([]);
      setIsListOpen(false);
      onChange(''); // Clear selection if input is cleared
    }
  };

  const handleOptionClick = (option) => {
    setInputValue(option.name);
    onChange(option.id);
    setIsListOpen(false);
  };

  return (
    <div className="relative" ref={wrapperRef}>
      <input
        type="text"
        value={inputValue}
        onChange={handleInputChange}
        onFocus={() => {
            setFilteredOptions(options);
            setIsListOpen(true);
        }}
        placeholder={placeholder}
        className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
      />
      {isListOpen && (
        <ul className="absolute z-10 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md mt-1 max-h-60 overflow-y-auto shadow-lg">
          {filteredOptions.length > 0 ? (
            filteredOptions.map(option => (
              <li
                key={option.id}
                onClick={() => handleOptionClick(option)}
                className="p-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-200"
              >
                {option.name}
              </li>
            ))
          ) : (
            <li className="p-2 text-gray-500">Aucun résultat</li>
          )}
        </ul>
      )}
    </div>
  );
}

export default AutocompleteSelect;
